#!/usr/bin/env bash
# HomesMenu — one-command AWS deploy (bash equivalent of deploy.ps1).
# Usage: ./deploy.sh [region]   (default ap-south-1)
set -euo pipefail

STACK_NAME="${STACK_NAME:-homesmenu}"
PROJECT_NAME="${PROJECT_NAME:-homesmenu}"
REGION="${1:-${REGION:-ap-south-1}}"
INSTANCE_TYPE="${INSTANCE_TYPE:-t3.small}"
KEYPAIR="${KEYPAIR:-}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(dirname "$SCRIPT_DIR")"
TEMPLATE="$SCRIPT_DIR/cloudformation/homesmenu.yaml"
PARAM_FILE="$SCRIPT_DIR/.deploy-params.json"

info(){ printf '\n==> %s\n' "$*"; }

command -v aws >/dev/null    || { echo "aws CLI not found"; exit 1; }
command -v docker >/dev/null || { echo "docker not found"; exit 1; }

ACCOUNT="$(aws sts get-caller-identity --query Account --output text)"
info "AWS account $ACCOUNT, region $REGION"

# stable DB passwords
randpw(){ LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 24; }
if [ -f "$PARAM_FILE" ]; then
  DB_PASS="$(grep -o '"DbPassword":"[^"]*"' "$PARAM_FILE" | cut -d'"' -f4)"
  DB_ROOT="$(grep -o '"DbRootPassword":"[^"]*"' "$PARAM_FILE" | cut -d'"' -f4)"
else
  DB_PASS="$(randpw)"; DB_ROOT="$(randpw)"
  printf '{"DbPassword":"%s","DbRootPassword":"%s"}\n' "$DB_PASS" "$DB_ROOT" > "$PARAM_FILE"
  info "Generated DB credentials -> $PARAM_FILE (gitignored, keep safe)"
fi

info "Resolving CloudFront origin prefix list"
PREFIX_ID="$(aws ec2 describe-managed-prefix-lists --region "$REGION" \
  --filters Name=prefix-list-name,Values=com.amazonaws.global.cloudfront.origin-facing \
  --query 'PrefixLists[0].PrefixListId' --output text 2>/dev/null || true)"
[ "$PREFIX_ID" = "None" ] && PREFIX_ID=""

info "Deploying CloudFormation stack '$STACK_NAME' (~5-10 min first run)"
OVERRIDES=( "ProjectName=$PROJECT_NAME" "InstanceType=$INSTANCE_TYPE" "DbPassword=$DB_PASS" "DbRootPassword=$DB_ROOT" )
[ -n "$PREFIX_ID" ] && OVERRIDES+=( "CloudFrontPrefixListId=$PREFIX_ID" )
[ -n "$KEYPAIR" ]   && OVERRIDES+=( "KeyPairName=$KEYPAIR" )
aws cloudformation deploy \
  --template-file "$TEMPLATE" \
  --stack-name "$STACK_NAME" \
  --region "$REGION" \
  --capabilities CAPABILITY_IAM \
  --no-fail-on-empty-changeset \
  --parameter-overrides "${OVERRIDES[@]}"

out(){ aws cloudformation describe-stacks --stack-name "$STACK_NAME" --region "$REGION" \
  --query "Stacks[0].Outputs[?OutputKey=='$1'].OutputValue" --output text; }
APP_URL="$(out AppUrl)"
API_BUILD_URL="$(out ApiBaseForFrontendBuild)"
BACKEND_URI="$(out BackendEcrUri)"
FRONTEND_URI="$(out FrontendEcrUri)"
INSTANCE_ID="$(out InstanceId)"
info "App URL: $APP_URL"

if [ "${SKIP_IMAGES:-}" = "1" ]; then info "SKIP_IMAGES=1 — infra only."; exit 0; fi

info "Logging in to ECR"
aws ecr get-login-password --region "$REGION" \
  | docker login --username AWS --password-stdin "$ACCOUNT.dkr.ecr.$REGION.amazonaws.com"

info "Building & pushing backend image"
docker build -t "$BACKEND_URI:latest" "$REPO_ROOT/backend"
docker push "$BACKEND_URI:latest"

info "Building & pushing frontend image (NEXT_PUBLIC_API_URL=$API_BUILD_URL)"
docker build --build-arg "NEXT_PUBLIC_API_URL=$API_BUILD_URL" -t "$FRONTEND_URI:latest" "$REPO_ROOT/frontend"
docker push "$FRONTEND_URI:latest"

info "Waiting for SSM, then rolling out on the instance"
for _ in $(seq 1 30); do
  st="$(aws ssm describe-instance-information --region "$REGION" \
    --filters "Key=InstanceIds,Values=$INSTANCE_ID" --query 'InstanceInformationList[0].PingStatus' --output text 2>/dev/null || true)"
  [ "$st" = "Online" ] && break; sleep 10
done
CMD_ID="$(aws ssm send-command --region "$REGION" --instance-ids "$INSTANCE_ID" \
  --document-name AWS-RunShellScript \
  --parameters 'commands=["/usr/local/bin/homesmenu-deploy"]' \
  --query Command.CommandId --output text)"
for _ in $(seq 1 60); do
  s="$(aws ssm get-command-invocation --region "$REGION" --command-id "$CMD_ID" --instance-id "$INSTANCE_ID" --query Status --output text 2>/dev/null || true)"
  case "$s" in Success|Failed|TimedOut|Cancelled) break;; esac; sleep 5
done
info "Rollout status: ${s:-unknown}"
info "Done. Open: $APP_URL  (first load ~1-2 min while containers boot & seed)"

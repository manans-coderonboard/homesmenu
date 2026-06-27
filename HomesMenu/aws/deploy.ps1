<#
.SYNOPSIS
  One-command deploy of HomesMenu to AWS (CloudFormation + ECR images + SSM rollout).

.DESCRIPTION
  1. Resolves the CloudFront origin prefix list (to lock the EC2 security group).
  2. Deploys / updates the CloudFormation stack (VPC, EC2, S3, CloudFront, ECR, IAM).
  3. Builds & pushes the backend and frontend Docker images to ECR.
     The frontend is built with NEXT_PUBLIC_API_URL = the app's CloudFront /api URL.
  4. Triggers `homesmenu-deploy` on the instance via SSM to pull & start the new images.

  DB passwords are generated once and cached in aws/.deploy-params.json (gitignored)
  so re-deploys reuse them (changing them would break the existing MySQL volume).

.EXAMPLE
  ./deploy.ps1 -Region ap-south-1
#>
[CmdletBinding()]
param(
  [string]$StackName    = "homesmenu",
  [string]$ProjectName  = "homesmenu",
  [string]$Region       = "ap-south-1",
  [ValidateSet("t3.micro","t3.small","t3.medium")]
  [string]$InstanceType = "t3.small",
  [string]$KeyPairName  = "",          # optional: EC2 key pair name for SSH
  [switch]$SkipImages                  # deploy infra only, skip docker build/push
)

$ErrorActionPreference = "Stop"
$scriptDir = $PSScriptRoot
$repoRoot  = Split-Path $scriptDir -Parent     # ...\HomesMenu
$template  = Join-Path $scriptDir "cloudformation\homesmenu.yaml"
$paramFile = Join-Path $scriptDir ".deploy-params.json"

function Info($m){ Write-Host "==> $m" -ForegroundColor Cyan }
function Ok($m){ Write-Host "    $m" -ForegroundColor Green }

# --- preflight ---
foreach ($bin in @("aws","docker")) {
  if (-not (Get-Command $bin -ErrorAction SilentlyContinue)) { throw "$bin not found on PATH." }
}
$account = (aws sts get-caller-identity --query Account --output text).Trim()
if (-not $account) { throw "Could not resolve AWS account — run 'aws configure' first." }
Ok "AWS account $account, region $Region"

# --- stable DB passwords ---
function New-AlphaNum([int]$n){ -join ((48..57)+(65..90)+(97..122) | Get-Random -Count $n | ForEach-Object {[char]$_}) }
if (Test-Path $paramFile) {
  $p = Get-Content $paramFile -Raw | ConvertFrom-Json
  Ok "Reusing cached DB credentials ($paramFile)"
} else {
  $p = [pscustomobject]@{ DbPassword = (New-AlphaNum 24); DbRootPassword = (New-AlphaNum 24) }
  $p | ConvertTo-Json | Set-Content $paramFile -Encoding utf8
  Ok "Generated DB credentials -> $paramFile (keep this safe, it's gitignored)"
}

# --- CloudFront origin-facing prefix list (locks the SG) ---
Info "Resolving CloudFront origin prefix list"
$prefixId = (aws ec2 describe-managed-prefix-lists --region $Region `
  --filters "Name=prefix-list-name,Values=com.amazonaws.global.cloudfront.origin-facing" `
  --query "PrefixLists[0].PrefixListId" --output text).Trim()
if ($prefixId -eq "None" -or -not $prefixId) { $prefixId = "" ; Ok "prefix list not found — SG will open 3000/8000 to 0.0.0.0/0" }
else { Ok "prefix list $prefixId" }

# --- deploy stack ---
Info "Deploying CloudFormation stack '$StackName' (this can take ~5-10 min on first run)"
$overrides = [System.Collections.Generic.List[string]]@(
  "ProjectName=$ProjectName",
  "InstanceType=$InstanceType",
  "DbPassword=$($p.DbPassword)",
  "DbRootPassword=$($p.DbRootPassword)"
)
if ($prefixId)    { $overrides.Add("CloudFrontPrefixListId=$prefixId") }
if ($KeyPairName) { $overrides.Add("KeyPairName=$KeyPairName") }
aws cloudformation deploy `
  --template-file $template `
  --stack-name $StackName `
  --region $Region `
  --capabilities CAPABILITY_IAM `
  --parameter-overrides $overrides `
  --no-fail-on-empty-changeset
if ($LASTEXITCODE -ne 0) { throw "CloudFormation deploy failed." }

# --- read outputs ---
$outJson = aws cloudformation describe-stacks --stack-name $StackName --region $Region --query "Stacks[0].Outputs" --output json
$outputs = $outJson | ConvertFrom-Json
function Out($k){ ($outputs | Where-Object { $_.OutputKey -eq $k }).OutputValue }
$appUrl       = Out "AppUrl"
$apiBuildUrl  = Out "ApiBaseForFrontendBuild"
$backendUri   = Out "BackendEcrUri"
$frontendUri  = Out "FrontendEcrUri"
$instanceId   = Out "InstanceId"
Ok "App URL:   $appUrl"
Ok "Instance:  $instanceId"

if ($SkipImages) { Info "SkipImages set — infra is up, skipping image build/push."; return }

# --- ECR login ---
Info "Logging in to ECR"
$registry = "$account.dkr.ecr.$Region.amazonaws.com"
(aws ecr get-login-password --region $Region) | docker login --username AWS --password-stdin $registry
if ($LASTEXITCODE -ne 0) { throw "docker login to ECR failed." }

# --- backend image ---
Info "Building & pushing backend image"
docker build -t "${backendUri}:latest" (Join-Path $repoRoot "backend")
if ($LASTEXITCODE -ne 0) { throw "backend build failed." }
docker push "${backendUri}:latest"
if ($LASTEXITCODE -ne 0) { throw "backend push failed." }

# --- frontend image (API URL baked at build time) ---
Info "Building & pushing frontend image (NEXT_PUBLIC_API_URL=$apiBuildUrl)"
docker build --build-arg "NEXT_PUBLIC_API_URL=$apiBuildUrl" -t "${frontendUri}:latest" (Join-Path $repoRoot "frontend")
if ($LASTEXITCODE -ne 0) { throw "frontend build failed." }
docker push "${frontendUri}:latest"
if ($LASTEXITCODE -ne 0) { throw "frontend push failed." }

# --- wait for SSM, then roll out on the instance ---
Info "Waiting for the instance to register with SSM"
for ($i=0; $i -lt 30; $i++) {
  $online = (aws ssm describe-instance-information --region $Region `
    --filters "Key=InstanceIds,Values=$instanceId" --query "InstanceInformationList[0].PingStatus" --output text 2>$null)
  if ($online -eq "Online") { break }
  Start-Sleep -Seconds 10
}
Info "Rolling out latest images via SSM (homesmenu-deploy)"
$cmdId = (aws ssm send-command --region $Region --instance-ids $instanceId `
  --document-name "AWS-RunShellScript" `
  --parameters 'commands=["/usr/local/bin/homesmenu-deploy"]' `
  --query "Command.CommandId" --output text).Trim()
for ($i=0; $i -lt 60; $i++) {
  $st = (aws ssm get-command-invocation --region $Region --command-id $cmdId --instance-id $instanceId --query "Status" --output text 2>$null)
  if ($st -in @("Success","Failed","TimedOut","Cancelled")) { break }
  Start-Sleep -Seconds 5
}
Ok "SSM rollout status: $st"

Write-Host ""
Info "Done. Open: $appUrl"
Write-Host "    (First page load may take ~1-2 min while the containers boot, migrate and seed.)"

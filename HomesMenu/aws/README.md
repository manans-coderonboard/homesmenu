# Deploying HomesMenu to AWS

A **cost-minimal**, single-instance deployment driven by one CloudFormation
template. No NAT gateway, no load balancer, no RDS, no Route 53 — just enough to
run the full stack with HTTPS and S3-backed uploads.

## Architecture

```
                       Viewers (HTTPS)
            ┌──────────────┴───────────────┐
     CloudFront APP                   CloudFront ASSETS
     <id>.cloudfront.net              <id>.cloudfront.net
       default → EC2:3000  (Next.js)    default → S3 (private, OAC)
       /api/*  → EC2:8000  (Laravel)          │
       /_next/static/* → cached               S3 uploads bucket
            │  HTTP origin                     (SSE, no public access)
            │  (SG locked to CloudFront)
            ▼
     EC2 t3.small  (public subnet + IGW, no NAT)
       docker compose:
         • frontend  Next.js   :3000
         • backend   Laravel   :8000  ──IAM role──► S3 (uploads) + ECR (pull)
         • db        MySQL 8   :3306  ──► /data/mysql on a RETAINED EBS volume
       managed via SSM Session Manager (no SSH required)
     images pulled from ECR (built & pushed by deploy.ps1)
```

**Why these choices** (all to minimise cost):
| Decision | Instead of | Saves |
|---|---|---|
| Single EC2 Docker host | ECS Fargate tasks | always-on task cost |
| Public subnet + IGW | private subnets + NAT gateway | **~$32/mo** NAT |
| CloudFront → EC2 origin | Application Load Balancer | **~$16/mo** ALB |
| MySQL container on EBS | RDS instance | **~$14/mo** RDS |
| `*.cloudfront.net` HTTPS | ACM cert + Route 53 domain | hosted-zone cost |

### Rough monthly cost (ap-south-1, low traffic)
- EC2 **t3.small** on-demand ≈ **$15** (or **t3.micro ≈ free** in the first-year free tier)
- EBS: 10 GB root + 20 GB data (gp3) ≈ **$2.5**
- S3 + ECR + CloudFront at low volume ≈ **a few cents** (CloudFront has a 1 TB/mo free tier)
- **≈ $18/mo**, or near-zero on free-tier t3.micro.

## Prerequisites
- **AWS CLI v2**, configured: `aws configure` (an IAM user/role allowed to create
  VPC/EC2/S3/CloudFront/ECR/IAM/SSM resources).
- **Docker** running locally (used to build the two images).
- That's it — no Composer/PHP/Node needed locally; images build inside Docker.

## Deploy

From this `aws/` folder:

```powershell
# Windows (PowerShell)
./deploy.ps1 -Region ap-south-1
```
```bash
# macOS / Linux / Git Bash
./deploy.sh ap-south-1
```

The script:
1. Generates & caches DB passwords in `aws/.deploy-params.json` (gitignored — **keep it**;
   reused on every redeploy so the MySQL volume keeps working).
2. Looks up the CloudFront origin prefix list and **locks the EC2 SG** to it.
3. `aws cloudformation deploy` — creates/updates the stack (~5–10 min first run).
4. Builds & pushes **backend** and **frontend** images to ECR. The frontend bakes
   `NEXT_PUBLIC_API_URL = https://<app>.cloudfront.net/api` so the browser, the API
   and the page are all same-origin (no CORS).
5. Runs `homesmenu-deploy` on the instance over **SSM** to pull the images and
   `docker compose up -d` (migrates + seeds on first boot).

When it finishes it prints the **App URL** (`https://<id>.cloudfront.net`). First load
takes 1–2 min while containers boot, migrate and seed.

> The very first `cloudformation deploy` boots the EC2 before any image exists in
> ECR; UserData starts the stack but the app containers simply wait. Step 4–5 push
> the images and roll them out. On later runs the same script just builds, pushes
> and rolls out the new images.

### Demo accounts (seeded)
`buyer@homesmenu.test` · `seller@homesmenu.test` · `dealer@homesmenu.test` ·
`admin@homesmenu.test` — password `password`.

## How S3 uploads work
`FILESYSTEM_DISK=s3` is set on the instance. The Laravel `UploadController` stores
to the bucket via the **instance IAM role** (no access keys on the box), and
`Storage::url()` builds public image URLs from `AWS_URL` = the **assets CloudFront**
domain. The bucket itself stays **private** (Block Public Access on); CloudFront
reads it through an **Origin Access Control**. Switching back to local disk is just
`FILESYSTEM_DISK=public` — the code is disk-agnostic.

## Day-2 operations
All via SSM (no SSH):
```bash
# open a shell on the box
aws ssm start-session --target <InstanceId> --region ap-south-1

# on the box:
cd /opt/homesmenu
docker compose ps
docker compose logs -f backend
docker compose logs -f frontend
sudo cat /var/log/homesmenu-bootstrap.log     # UserData bootstrap log
homesmenu-deploy                               # re-pull latest images & restart
```
Redeploy after code changes: just re-run `./deploy.ps1` (rebuilds, pushes, rolls out).

## Updating / scaling later (documented upgrade paths)
- **Managed DB:** add an `AWS::RDS::DBInstance` (db.t4g.micro) + a 2-AZ subnet group,
  point `DB_HOST` at its endpoint, drop the `db` service. ~+$14/mo, gains backups/HA.
- **Custom domain + your own cert:** add `Aliases` + an ACM cert (in **us-east-1**)
  to both distributions and a CNAME at your DNS provider — no Route 53 required.
- **Zero-downtime / horizontal scale:** move to an ALB + an ECS Fargate service
  (the Dockerfiles already work); reuse the same ECR repos and S3 bucket.
- **Tighten CORS:** the API uses `CORS_ALLOWED_ORIGINS=*` (safe with Bearer-token
  auth, same-origin in practice). Set it to the app domain to be strict.

## Teardown
```bash
aws cloudformation delete-stack --stack-name homesmenu --region ap-south-1
```
The **S3 uploads bucket and the MySQL EBS volume are retained** (DeletionPolicy:
Retain) so you don't lose data by accident. Delete them by hand when you're sure:
```bash
aws s3 rb s3://<UploadsBucketName> --force
aws ec2 delete-volume --volume-id <vol-id>
```
ECR repos empty-on-delete, so images are removed with the stack.

## Files
| File | Purpose |
|---|---|
| `cloudformation/homesmenu.yaml` | the whole stack (VPC, EC2, S3, 2× CloudFront, ECR, IAM, SG) |
| `deploy.ps1` / `deploy.sh` | provision + build/push images + roll out |
| `docker-compose.prod.yml` | reference copy of what the instance runs |
| `.deploy-params.json` | generated DB creds (gitignored) |

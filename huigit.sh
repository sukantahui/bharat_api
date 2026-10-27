#!/usr/bin/env bash

set -e

# Check if commit message is provided
if [ -z "$1" ]; then
  echo "❌ Please provide a commit message."
  echo "Usage: ./gitpush.sh \"your commit message\""
  exit 1
fi

COMMIT_MSG="$1"

echo "📦 Adding files..."
git add .

echo "📝 Committing..."
git commit -m "$COMMIT_MSG"

echo "⬇️ Pulling latest changes..."
git pull --rebase

echo "⬆️ Pushing to remote..."
git push

echo "✅ Done!"

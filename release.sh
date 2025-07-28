#!/usr/bin/env bash
set -e

# .envファイルを読み込む
source ./.env
sed -i '' "s/Stable tag: .*/Stable tag: ${LINE_CONNECT_VERSION}/" readme.txt
sed -i '' "s/Version: .*/Version: ${LINE_CONNECT_VERSION}/" lineconnect.php
sed -i '' "s/const VERSION = '.*/const VERSION = '${LINE_CONNECT_VERSION}';/" src/Core/LineConnect.php

# ワーキングディレクトリを保存して後で利用可能にする
WORKING_DIR=`pwd`

# frontend/richmenuに移動して、ビルドする
cd frontend/richmenu
npm run build
cd $WORKING_DIR

# line-bulkmessageに移動して、ビルドする
cd frontend/bulkmessage
npm run build
cd $WORKING_DIR

# line-dmに移動して、ビルドする
cd frontend/dm
npm run build
cd $WORKING_DIR

cd frontend/rjsf
npm run build
cd $WORKING_DIR

cd frontend/action-execute
npm run build
cd $WORKING_DIR

# ダッシュボードのビルド
cd frontend/dashboard
npm run build
cd $WORKING_DIR

## documentのビルド
cd document
npm run build
cd $WORKING_DIR

# テストを実行
#composer test

# 全てステージにのせる
git add -A;

# コミット対象のファイルを確認
git status;
read -p "Commit with this content. OK? (y/N): " yesno
case "$yesno" in
# yes
[yY]*)  git commit -F CHANGES.md;
		CULLENT_BRANCH=`git rev-parse --abbrev-ref HEAD`;
		git push origin ${CULLENT_BRANCH};
		git tag v${LINE_CONNECT_VERSION};
		git push origin v${LINE_CONNECT_VERSION};
		gh release create v${LINE_CONNECT_VERSION} -d -t "v${LINE_CONNECT_VERSION} Release" -F CHANGES.md;;
# no
*) echo "Quit." ;;
esac



#!/usr/bin/env bash

set -e

BASEPATH=$(cd `dirname $0`; cd ../; pwd)
# echo $BASEPATH;
# exit;
REPOS=$@

function init()
{
    local REPO=$1
    local DOC_PATH=$BASEPATH/docs/zh_CN/components

    mkdir -p $DOC_PATH

    local DOC_FILE=$DOC_PATH/$REPO.md

    if [ ! -f $DOC_FILE ]; then
        echo "# $REPO" > $DOC_FILE
        echo "" >> $DOC_FILE
        echo "## 安装" >> $DOC_FILE
        echo "" >> $DOC_FILE
        echo '```shell' >> $DOC_FILE
        echo "composer require friendsofhyperf/$REPO" >> $DOC_FILE
        echo '```' >> $DOC_FILE
        echo "" >> $DOC_FILE
    fi
}

if [[ $# -eq 0 ]]; then
    REPOS=$(ls $BASEPATH/src)
fi

for REPO in $REPOS ; do
    init $REPO
done

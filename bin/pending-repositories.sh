#!/usr/bin/env bash

set -e

BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)
REPOS=$@

function cleanup()
{
    local REPO=$1
    local FILES="src/$REPO/.git src/$REPO/.vscode src/$REPO/.github src/$REPO/vendor/ src/$REPO/.gitignore src/$REPO/.php-cs-fixer.php src/$REPO/.phpunit* src/$REPO/composer.lock src/$REPO/phpunit.* src/$REPO/phpstan* src/$REPO/.phpstorm.meta* src/$REPO/pint.json"

    for FILE in $FILES; do
        echo "Removing ${FILE}"
        rm -rf ${FILE}
    done
}

function pending()
{
    local REPO=$1

    echo "Copying .github to ${REPO}"
    cp -rf bin/stubs/.github src/$REPO

    echo "Copying .gitattributes to ${REPO}"
    cp -rf bin/stubs/.gitattributes src/$REPO

    # if the LICENSE not exists, copy it
    if [[ ! -f src/$REPO/LICENSE ]]; then
        echo "Copying LICENSE to ${REPO}"
        cp -rf ./LICENSE src/$REPO
    fi

    return
}

if [[ $# -eq 0 ]]; then
    REPOS=$(ls $BASEPATH)
fi

for REPO in $REPOS ; do
    echo "Pending ${REPO} ..."

    cleanup "$REPO"

    pending "$REPO"

    echo ""
done

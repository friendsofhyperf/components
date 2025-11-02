#!/usr/bin/env bash

set -e

# Usage:
#  ./bin/release.sh v[version]
#
# Example:
# ./bin/release.sh v1.0.0

# Make sure the release tag is provided.
if (( "$#" != 1 ))
then
    echo "Tag has to be provided."

    exit 1
fi

NOW=$(date +%s)
RELEASE_BRANCH="3.2"
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)
VERSION=$1

# Make sure current branch and release branch match.
if [[ "$RELEASE_BRANCH" != "$CURRENT_BRANCH" ]]
then
    echo "Release branch ($RELEASE_BRANCH) does not match the current active branch ($CURRENT_BRANCH)."

    exit 1
fi

# Make sure the working directory is clear.
if [[ ! -z "$(git status --porcelain)" ]]
then
    echo "Your working directory is dirty. Did you forget to commit your changes?"

    exit 1
fi

# Make sure latest changes are fetched first.
git fetch origin

# Make sure that release branch is in sync with origin.
if [[ $(git rev-parse HEAD) != $(git rev-parse origin/$RELEASE_BRANCH) ]]
then
    echo "Your branch is out of date with its upstream. Did you forget to pull or push any changes before releasing?"

    exit 1
fi

# Always prepend with "v"
if [[ $VERSION != v*  ]]
then
    VERSION="v$VERSION"
fi

REMOTES=$(ls $BASEPATH)

# Delete the old release tag.
# git push --delete origin $VERSION
# git push origin :refs/tags/$VERSION
# git tag --delete $VERSION

# Tag Framework
git tag $VERSION
git push origin --tags

# Beta Components
BETA_COMPONENTS=(
    "mail"
    "notification"
    "notification-easysms"
    "notification-mail"
)

# Tag Components
for REMOTE in $REMOTES
do
    # Skip the beta components
    # if [[ " ${BETA_COMPONENTS[@]} " =~ " $REMOTE " ]]
    # then
    #     echo "Skipping $REMOTE";
    #     continue;
    # fi

    echo ""
    echo ""
    echo "Releasing [ ${REMOTE} ]";

    TMP_DIR="/tmp/friendsofhyperf-split"
    REMOTE_URL="git@github.com:friendsofhyperf/${REMOTE}.git"

    rm -rf $TMP_DIR;
    mkdir $TMP_DIR;

    (
        cd $TMP_DIR;

        git clone $REMOTE_URL .
        git checkout "${RELEASE_BRANCH}";

        if [[ $(git log --pretty="%d" -n 1 | grep tag --count) -eq 0 ]]; then
            echo "Pushing ${REMOTE}";
            git tag $VERSION
            git push origin --tags
        fi
    )

done

TIME=$(echo "$(date +%s) - $NOW" | bc)

printf "Execution time: %f seconds" $TIME
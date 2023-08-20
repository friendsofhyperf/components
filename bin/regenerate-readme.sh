#!/usr/bin/env bash

BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)
REPOS=$(ls $BASEPATH)

# https://poser.pugx.org/show/friendsofhyperf/components

function github_actions() {
    echo "[![Latest Test](https://github.com/friendsofhyperf/$1/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/$1/actions)"
}

function repository() {
    echo "[$1](https://github.com/friendsofhyperf/$1)"
}

function latest_stable_version() {
    echo "[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/$1/v)](https://packagist.org/packages/friendsofhyperf/$1)"
}

function latest_unstable_version() {
    echo "[![Latest Unstable Version](https://poser.pugx.org/friendsofhyperf/$1/v/unstable)](https://packagist.org/packages/friendsofhyperf/$1)"
}

function total_downloads() {
    echo "[![Total Downloads](https://poser.pugx.org/friendsofhyperf/$1/downloads)](https://packagist.org/packages/friendsofhyperf/$1)"
}

function monthly_downloads() {
    echo "[![Monthly Downloads](https://poser.pugx.org/friendsofhyperf/$1/d/monthly)](https://packagist.org/packages/friendsofhyperf/$1)"
}

function daily_downloads() {
    echo "[![Daily Downloads](https://poser.pugx.org/friendsofhyperf/$1/d/daily)](https://packagist.org/packages/friendsofhyperf/$1)"
}

function php_version_require() {
    echo "[![PHP Version Require](https://poser.pugx.org/friendsofhyperf/$1/require/php)](https://packagist.org/packages/friendsofhyperf/$1)"
}

function license() {
    echo "[![License](https://poser.pugx.org/friendsofhyperf/$1/license)](https://packagist.org/packages/friendsofhyperf/$1)"
}

echo "# friendsofhyperf/components"
echo 
printf "%s %s %s %s\n" \
    "$(github_actions components)" \
    "$(latest_stable_version components)" \
    "$(license components)" \
    "$(php_version_require components)"
echo
echo "The most popular components for Hyperf."
echo 
echo "## Repositories"
echo
echo "|Repository|Stable Version|Total Downloads|Monthly Downloads|"
echo "|--|--|--|--|"

for REPO in ${REPOS}; do
    printf "|%s|%s|%s|%s|%s|\n" \
        "$(repository ${REPO})" \
        "$(latest_stable_version ${REPO})" \
        "$(total_downloads ${REPO})" \
        "$(monthly_downloads ${REPO})"
done

echo

cat <<EOF
## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
EOF
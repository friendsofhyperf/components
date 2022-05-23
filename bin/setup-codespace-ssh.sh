#!/usr/bin/env bash

mkdir -p ~/.ssh/ \
    && echo -e $ID_ED25519_PRIV > ~/.ssh/id_ed25519 \
    && chown vscode. /home/vscode/.ssh/id_ed25519 \
    && chmod 600 ~/.ssh/id_ed25519 \
    && ssh-keyscan github.com >> ~/.ssh/known_hosts
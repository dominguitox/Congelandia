#!/usr/bin/env bash
set -euo pipefail

# Solo se usa en un runner desechable. Nunca se ejecuta en el hosting ni en el PC.
: "${RUNNER_TEMP:?}" "${PACKAGE_DIR:?}" "${VERSION:?}" "${SOURCE_COMMIT:?}"
deployment="$RUNNER_TEMP/deploy-worktree"
if git show-ref --verify --quiet refs/remotes/origin/deploy; then
    git worktree add --detach "$deployment" origin/deploy
else
    git worktree add --detach "$deployment" "$SOURCE_COMMIT"
    git -C "$deployment" checkout --orphan prepared-deploy
fi
git -C "$deployment" rm -r -f --ignore-unmatch --quiet .
cp -a "$PACKAGE_DIR/." "$deployment/"
git -C "$deployment" add --all --force
if git -C "$deployment" diff --cached --quiet; then
    echo 'La entrega solicitada ya está en deploy.'
    exit 0
fi
git -C "$deployment" -c user.name='github-actions[bot]' -c user.email='41898282+github-actions[bot]@users.noreply.github.com' commit -m "Deploy v$VERSION ($SOURCE_COMMIT)"
if [[ "${RESTORE:-false}" == 'true' ]]; then
    git -C "$deployment" push origin HEAD:refs/heads/deploy
else
    git -C "$deployment" tag "deploy-v$VERSION"
    git tag "v$VERSION" "$SOURCE_COMMIT"
    # Una sola operación: rama y etiquetas avanzan juntas o no avanza ninguna.
    git -C "$deployment" push --atomic origin HEAD:refs/heads/deploy "refs/tags/deploy-v$VERSION" "refs/tags/v$VERSION"
fi

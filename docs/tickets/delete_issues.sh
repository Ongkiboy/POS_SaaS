
#!/usr/bin/env bash
set -euo pipefail

# Pastikan berada di repository yang benar
REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner')

echo "Repository target: $REPO"
echo "Script ini akan menghapus SEMUA issue biasa di repository tersebut."
echo "Pull request tidak akan ikut dihapus."
read -r -p "Ketik DELETE untuk melanjutkan: " CONFIRM

if [[ "$CONFIRM" != "DELETE" ]]; then
    echo "Dibatalkan."
    exit 0
fi

# Ambil nomor seluruh issue, termasuk issue yang sudah ditutup.
mapfile -t ISSUES < <(
    gh issue list \
        --repo "$REPO" \
        --state all \
        --limit 1000 \
        --json number \
        --jq '.[].number'
)

echo "Jumlah issue ditemukan: ${#ISSUES[@]}"

for ISSUE in "${ISSUES[@]}"; do
    echo "Menghapus issue #$ISSUE ..."

    gh api \
        --method DELETE \
        "repos/$REPO/issues/$ISSUE" \
        || {
            echo "Gagal menghapus issue #$ISSUE"
            exit 1
        }
done

echo "Selesai."

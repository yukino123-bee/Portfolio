#!/usr/bin/env bash
set -euo pipefail

project_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
data_dir="$project_root/storage/private-mysql"
socket_file="$project_root/storage/mysql.sock"
pid_file="$project_root/storage/mysql.pid"
log_file="$project_root/storage/private-mysql.log"

if mysqladmin --protocol=socket --socket="$socket_file" -uroot ping --silent 2>/dev/null; then
    exit 0
fi

mkdir -p "$data_dir"
if [[ ! -d "$data_dir/mysql" ]]; then
    mysqld --initialize-insecure --datadir="$data_dir" --tmpdir=/tmp --log-error="$project_root/storage/private-mysql-init.log"
fi

mysqld \
    --datadir="$data_dir" \
    --tmpdir=/tmp \
    --port=3307 \
    --bind-address=127.0.0.1 \
    --socket="$socket_file" \
    --pid-file="$pid_file" \
    --log-error="$log_file" \
    --mysqlx=OFF \
    --daemonize

for _ in {1..30}; do
    if mysqladmin --protocol=socket --socket="$socket_file" -uroot ping --silent 2>/dev/null; then
        exit 0
    fi
    sleep 0.2
done

echo "The portfolio database did not start. Check $log_file" >&2
exit 1

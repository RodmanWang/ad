
#!/bin/bash

# ==========================================
# 基础环境
# ==========================================

if [ -f /etc/profile ]; then
    source /etc/profile
fi

# 始终从脚本所在目录执行，避免工作目录不同导致路径错误
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR" || exit 1

ORIGIN_DIR="$SCRIPT_DIR/origin-files"

# ==========================================
# 数据源
# ==========================================

easylist=(
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_2_Base/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_4_Social/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_7_Japanese/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_11_Mobile/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_19_Annoyances_Popups/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_20_Annoyances_MobileApp/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_21_Annoyances_Other/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_22_Annoyances_Widgets/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_224_Chinese/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/HostlistsRegistry/refs/heads/main/filters/regional/filter_25_KOR_List-KR/filter.txt"
)

# ad-hosts.txt 使用仓库中的本地文件，不重复从 GitHub 下载
hosts=(
  "https://malware-filter.gitlab.io/malware-filter/urlhaus-filter-hosts-online.txt"
  "https://pgl.yoyo.org/adservers/serverlist.php?showintro=0;hostformat=hosts&useip=0.0.0.0"
)

strict_hosts=(
  "https://raw.githubusercontent.com/AdguardTeam/HostlistsRegistry/refs/heads/main/filters/security/filter_8_NoCoin/filter.txt"
)

dead_hosts=(
  "https://raw.githubusercontent.com/notracking/hosts-blocklists-scripts/master/domains.dead.txt"
  "https://raw.githubusercontent.com/notracking/hosts-blocklists-scripts/master/hostnames.dead.txt"
)

# ==========================================
# 下载环境初始化
# ==========================================

mkdir -p "$ORIGIN_DIR" || {
    echo "ERROR: 无法创建目录：$ORIGIN_DIR"
    exit 1
}

# 下载到临时目录，全部成功后才替换旧文件
DOWNLOAD_TMP="$(mktemp -d "$ORIGIN_DIR/.download.XXXXXX")" || {
    echo "ERROR: 无法创建下载临时目录"
    exit 1
}

cleanup_download() {
    if [ -n "${DOWNLOAD_TMP:-}" ] && [ -d "$DOWNLOAD_TMP" ]; then
        rm -rf -- "$DOWNLOAD_TMP"
    fi
}

trap cleanup_download EXIT

# ==========================================
# 通用下载函数
# 最多重试 4 次，等待时间逐次增加
# ==========================================

download_group() {
    local prefix="$1"
    local index="$2"
    shift 2

    local url
    local target
    local attempt
    local success

    for url in "$@"; do
        target="$DOWNLOAD_TMP/${prefix}${index}.txt"
        success=false

        echo
        echo "=========================================="
        echo "开始下载：${prefix}${index}.txt"
        echo "来源：$url"
        echo "=========================================="

        for attempt in 1 2 3 4; do
            echo "下载尝试：${attempt}/4"

            rm -f -- "${target}.part"

            if curl \
                --fail \
                --location \
                --connect-timeout 20 \
                --max-time 90 \
                --silent \
                --show-error \
                --output "${target}.part" \
                "$url"; then

                if [ -s "${target}.part" ]; then
                    mv -- "${target}.part" "$target"
                    success=true
                    echo "下载成功：${prefix}${index}.txt"
                    break
                else
                    echo "WARNING: 下载结果为空文件"
                fi
            else
                echo "WARNING: curl 下载失败"
            fi

            rm -f -- "${target}.part"

            if [ "$attempt" -lt 4 ]; then
                local wait_seconds=$((5 * (2 ** (attempt - 1))))
                echo "等待 ${wait_seconds} 秒后重试..."
                sleep "$wait_seconds"
            fi
        done

        if [ "$success" != true ]; then
            echo
            echo "ERROR: 下载失败：${prefix}${index}.txt"
            echo "本次任务终止，原有下载文件不会被删除。"
            return 1
        fi

        index=$((index + 1))
    done

    return 0
}

# ==========================================
# 检查并准备本地 hosts 文件
# ==========================================

LOCAL_HOSTS="$ORIGIN_DIR/ad-hosts.txt"

if [ ! -s "$LOCAL_HOSTS" ]; then
    echo "ERROR: 本地文件不存在或为空：$LOCAL_HOSTS"
    exit 1
fi

cp -- "$LOCAL_HOSTS" "$DOWNLOAD_TMP/hosts0.txt" || {
    echo "ERROR: 无法复制本地 ad-hosts.txt"
    exit 1
}

echo "已使用仓库中的本地文件：ad-hosts.txt"

# ==========================================
# 下载全部数据源
# 任意一个数据源最终失败，本次更新即终止
# ==========================================

download_group easylist 0 "${easylist[@]}" || exit 1
download_group hosts 1 "${hosts[@]}" || exit 1
download_group strict-hosts 0 "${strict_hosts[@]}" || exit 1
download_group dead-hosts 0 "${dead_hosts[@]}" || exit 1

echo
echo "=========================================="
echo "所有数据源下载成功"
echo "=========================================="

# ==========================================
# 全部下载成功后，才替换旧下载文件
# ==========================================

echo "开始更新 origin-files 中的下载文件..."

find "$ORIGIN_DIR" -maxdepth 1 -type f \
    \( \
        -name 'easylist*.txt' -o \
        -name 'hosts*.txt' -o \
        -name 'strict-hosts*.txt' -o \
        -name 'dead-hosts*.txt' \
    \) -delete || {
        echo "ERROR: 清理旧下载文件失败"
        exit 1
    }

for file in "$DOWNLOAD_TMP"/*.txt; do
    [ -f "$file" ] || continue

    mv -- "$file" "$ORIGIN_DIR/" || {
        echo "ERROR: 替换文件失败：$file"
        exit 1
    }
done

echo "下载文件更新完成"

# ==========================================
# 规则文件预处理
# ==========================================

cd "$ORIGIN_DIR" || exit 1

echo "开始处理 hosts 数据..."

cat hosts*.txt | grep -v -E "^((#.*)|(\s*))$" \
 | grep -v -E "^[0-9\.:]+\s+(ip6\-)?(localhost|loopback)$" \
 | sed 's/0.0.0.0/127.0.0.1/g' \
 | sed 's/::/127.0.0.1/g' \
 | sort \
 | uniq > base-src-hosts.txt

echo "开始处理 strict-hosts 数据..."

cat strict-hosts*.txt | grep -v -E "^((#.*)|(\s*))$" \
 | grep -v -E "^[0-9\.:]+\s+(ip6\-)?(localhost|loopback)$" \
 | sed 's/0.0.0.0/127.0.0.1/g' \
 | sed 's/::/127.0.0.1/g' \
 | sort \
 | uniq > base-src-strict-hosts.txt

echo "开始处理 dead-hosts 数据..."

cat dead-hosts*.txt | grep -v -E "^(#|\!)" \
 | sort \
 | uniq > base-dead-hosts.txt

echo "开始处理 EasyList 数据..."

cat easylist*.txt | grep -E "^\|\|[^\*\^]+?\^" \
 | sort | uniq > base-src-easylist.txt

cat easylist*.txt | grep -E "^\|\|?([^\^=\/:]+)?\*([^\^=\/:]+)?\^" \
 | sort | uniq > wildcard-src-easylist.txt

cat easylist*.txt | grep -E "^@@\|\|?[^\^=\/:]+?\^([^\/=\*]+)?$" \
 | sort | uniq > whiterule-src-easylist.txt

# ==========================================
# 生成最终规则
# ==========================================

cd "$SCRIPT_DIR" || exit 1

echo
echo "=========================================="
echo "开始生成最终规则"
echo "=========================================="

php make-addr.php || {
    echo "ERROR: make-addr.php 执行失败"
    exit 1
}

echo

php ./tools/easylist-extend.php ../ad-easylist.txt || {
    echo "ERROR: easylist-extend.php 执行失败"
    exit 1
}

echo
echo "=========================================="
echo "start-ci.sh 执行完成"
echo "=========================================="

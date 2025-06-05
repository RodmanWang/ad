#!/bin/bash

source /etc/profile

cd $(cd "$(dirname "$0")";pwd)

easylist=(
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_2_Base/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_3_Spyware/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_4_Social/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_7_Japanese/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_11_Mobile/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_19_Annoyances_Popups/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_20_Annoyances_MobileApp/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_21_Annoyances_Other/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_22_Annoyances_Widgets/filter.txt"
  "https://raw.githubusercontent.com/AdguardTeam/FiltersRegistry/master/filters/filter_224_Chinese/filter.txt"
)

hosts=(
  "https://raw.githubusercontent.com/RodmanWang/ad/refs/heads/master/scripts/origin-files/ad-hosts.txt"
  "https://malware-filter.gitlab.io/malware-filter/urlhaus-filter-hosts-online.txt"
  "https://pgl.yoyo.org/adservers/serverlist.php?showintro=0;hostformat=hosts&useip=0.0.0.0"
)

strict_hosts=(
  "https://raw.githubusercontent.com/hoshsadiq/adblock-nocoin-list/master/hosts.txt"
)

dead_hosts=(
  "https://raw.githubusercontent.com/notracking/hosts-blocklists-scripts/master/domains.dead.txt"
  "https://raw.githubusercontent.com/notracking/hosts-blocklists-scripts/master/hostnames.dead.txt"
)

rm -f ./origin-files/easylist*
rm -f ./origin-files/hosts*
rm -f ./origin-files/strict-hosts*
rm -f ./origin-files/dead-hosts*

for i in "${!easylist[@]}"
do
  echo "开始下载 easylist${i}..."
  curl -o "./origin-files/easylist${i}.txt" --connect-timeout 60 -s "${easylist[$i]}"
  # shellcheck disable=SC2181
  if [ $? -ne 0 ];then
    echo '下载失败，请重试'
    exit 1
  fi
done

for i in "${!hosts[@]}"
do
  echo "开始下载 hosts${i}..."
  curl -o "./origin-files/hosts${i}.txt" --connect-timeout 60 -s "${hosts[$i]}"
  # shellcheck disable=SC2181
  if [ $? -ne 0 ];then
    echo '下载失败，请重试'
    exit 1
  fi
done

for i in "${!strict_hosts[@]}"
do
  echo "开始下载 strict-hosts${i}..."
  curl -o "./origin-files/strict-hosts${i}.txt" --connect-timeout 60 -s "${strict_hosts[$i]}"
  # shellcheck disable=SC2181
  if [ $? -ne 0 ];then
    echo '下载失败，请重试'
    exit 1
  fi
done

for i in "${!dead_hosts[@]}"
do
  echo "开始下载 dead-hosts${i}..."
  curl -o "./origin-files/dead-hosts${i}.txt" --connect-timeout 60 -s "${dead_hosts[$i]}"
  # shellcheck disable=SC2181
  if [ $? -ne 0 ];then
    echo '下载失败，请重试'
    exit 1
  fi
done


cd origin-files

cat hosts*.txt | grep -v -E "^((#.*)|(\s*))$" \
 | grep -v -E "^[0-9\.:]+\s+(ip6\-)?(localhost|loopback)$" \
 | sed s/0.0.0.0/127.0.0.1/g | sed s/::/127.0.0.1/g | sort \
 | uniq >base-src-hosts.txt

cat strict-hosts*.txt | grep -v -E "^((#.*)|(\s*))$" \
 | grep -v -E "^[0-9\.:]+\s+(ip6\-)?(localhost|loopback)$" \
 | sed s/0.0.0.0/127.0.0.1/g | sed s/::/127.0.0.1/g | sort \
 | uniq >base-src-strict-hosts.txt

cat dead-hosts*.txt | grep -v -E "^(#|\!)" \
 | sort \
 | uniq >base-dead-hosts.txt


cat easylist*.txt | grep -E "^\|\|[^\*\^]+?\^" | sort | uniq >base-src-easylist.txt
cat easylist*.txt | grep -E "^\|\|?([^\^=\/:]+)?\*([^\^=\/:]+)?\^" | sort | uniq >wildcard-src-easylist.txt
cat easylist*.txt | grep -E "^@@\|\|?[^\^=\/:]+?\^([^\/=\*]+)?$" | sort | uniq >whiterule-src-easylist.txt

cd ../

php make-addr.php
echo
php ./tools/easylist-extend.php ../ad-easylist.txt

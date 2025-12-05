#!/bin/bash

sh eth_stop.sh
cat /dev/null > log.txt
nohup sh eth_start.sh > log.txt 2>&1 &

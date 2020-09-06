#!/bin/sh
(cd src/ && ./../vendor/bin/Ubiquity serve -t=workerman -p=8095 -h=127.0.0.1)
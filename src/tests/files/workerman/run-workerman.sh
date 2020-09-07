#!/bin/sh
(cd src/ && ./../vendor/bin/Ubiquity serve -t=workerman -p=8095 -h=worker.local)
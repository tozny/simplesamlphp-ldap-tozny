#!/bin/sh

java -jar /var/selenium-server-standalone-2.37.0.jar -htmlsuite "*firefox" "http://sandbox.tozny.com" "sandbox_test_suite.html"  "results/results.html"

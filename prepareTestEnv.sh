#!/bin/bash
USER_STORY=$1
PULL_REQUEST=$2

git checkout master
git branch $USER_STORY
git checkout $USER_STORY
git merge origin pr/$PULL_REQUEST -m "merged to test" 



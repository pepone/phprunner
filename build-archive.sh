#!/bin/bash
git archive --format tar --prefix=PhpRunner-1.0.0/ master | gzip > ../PhpRunner-1.0.0.tar.gz

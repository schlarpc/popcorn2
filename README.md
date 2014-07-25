Popcorn 2
=========

Pretty neat-o webm synchronized streaming solution in PHP.
Uses stream.m and ffmpeg to do the hard parts.

Features:
* Conversion and streaming in webm format
* Integrated movie/TV library (powered by Shoebox technology)
* Video source plugins
* Server side pause/resume support
* Stream resynchronization
* [API that makes an attempt to be RESTful](APIDOCS.md)

Setup:
* Haha good luck
* You need to compile ffmpeg yourself because the static builds are all awful
* And stream.m too because there's some issues in the last release that make it crash
* No, I don't have patches for it available yet

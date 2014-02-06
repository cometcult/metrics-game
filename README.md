MetricsGame
===========

[![Build Status](https://travis-ci.org/cometcult/metrics-game.png?branch=master)](https://travis-ci.org/cometcult/metrics-game)

Gamify your git experience!

![Sample output on HipChat](https://dl.dropboxusercontent.com/u/757052/game-metrics.png)

The Comet Cult Metrics Game is a script that should be runned daily. It gathers the amount of commits in your company's github and/or bitbucket account and sends the metrics to HipChat.

If you like it feel free to contribute with other metrics or notifications :)

Configuring & Installing
------------------------

```bash
cp config.yml.dist config.yml
# supply your settings in config.yml
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

Running
-------
To run the game after you have it configured type:

```bash
php app/console thecometcult:metrics
```

Specs
-----

To run the specs just type:

```bash
./bin/phpspec run
```

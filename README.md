# Hungarian Algorithm

[![Latest Version](http://img.shields.io/packagist/v/elgigi/hungarian-algorithm.svg?style=flat-square)](https://github.com/ElGigi/HungarianAlgorithm/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/ElGigi/HungarianAlgorithm/tests.yml?branch=main&style=flat-square)](https://github.com/ElGigi/HungarianAlgorithm/actions/workflows/tests.yml?query=branch%3Amain)
[![Codacy Grade](https://img.shields.io/codacy/grade/4c4a5d058b58475bb6be046555ceb09f/main.svg?style=flat-square)](https://app.codacy.com/gh/ElGigi/HungarianAlgorithm)
[![Total Downloads](https://img.shields.io/packagist/dt/elgigi/hungarian-algorithm.svg?style=flat-square)](https://packagist.org/packages/elgigi/hungarian-algorithm)

An implementation of the Hungarian algorithm in PHP. The Hungarian algorithm can be used to find the optimal (minimal
cost) assignment between two types of entities given a cost matrix. The Hungarian algorithm is also known as the
Kuhn–Munkres algorithm or Munkres assignment algorithm.

**This fork is based on [rpfk's](https://github.com/rpfk/Hungarian) implementation.
Addition of original repository is visible on changelog.**

## Installation

You can install the library with [Composer](https://getcomposer.org/):

```bash
composer require elgigi/hungarian-algorithm
```

## Usage

Define a square matrix with scores as input for the Hungarian class. A square matrix must be an array consisting of n
arrays (rows), with each array consisting of n scores.
The key of each element in the row array must be equal to the key of the column.

```php
// Define the score matrix as n arrays consisting of n numerical values
$array = [
    [1, ··· ,1],
    ···
    [3, ··· ,0],
];

// Create a new Hungarian problem using the score matrix as input
$hungarian  = new Hungarian($array);

// Solve the problem using the Hungarian algorithm and get the solution as an array with the row and column as key and value, respectively
$allocation = $hungarian->solve();
```

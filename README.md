# php-dataframe

A lightweight dataframe handling package for php.

The php-dataframe package is not a data science package, it just tries copy the main features of pandas' DataFrame.

## Software requirements

PHP version 7.2 or newer to develop using php-dataframe.

## Installation

Use [composer](https://getcomposer.org) to install PhpSpreadsheet into your project:

```sh
composer require nagyatka/php-dataframe
```

## Usage


### Create DataFrame

#### Create a simple DataFrame object using a 2D array:
```php
$df = new DataFrame([[0,1],[2,3]]);
print($df);
```
Output:
```sh
     |0         |1         |
============================
0    |0         |1         |
1    |2         |3         |
Shape: 2x2
```

#### Create a simple DataFrame object using a 2D array, and column names:
```php
$df = new DataFrame([[0,1],[2,3]], ["a", "b"]);
print($df);
```
Output:
```sh
     |a         |b         |
============================
0    |0         |1         |
1    |2         |3         |
Shape: 2x2
```


#### Create a simple DataFrame object using a 2D array, column names, and indices:
```php
$df = new DataFrame([[0,1],[2,3]], ["a", "b"], ["e", "f"]);
print($df);
```
Output:
```sh
     |a         |b         |
============================
e    |0         |1         |
f    |2         |3         |
Shape: 2x2
```

### Indexing and selecting data

#### Selecting one column of a DataFrame
```php
$df = new DataFrame([[0,1],[2,3]], ["a", "b"], ["e", "f"]);
print($df["a"]);
```
Selecting column "a" the DataFrame object returns with a Series object.

Output:
```sh
Series(Name=a, length=2){[
	e: 0,
	f: 2,
]}
```

#### Selecting multiple columns of a DataFrame
```php
$df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
print($df[\PHPDataFrame\cols(["b", "c"])]);
```
Because php does not support array object as key, the "cols" helper function needs to be used to select multi columns.

Output:
```sh
     |b         |c         |
============================
e    |1         |2         |
f    |4         |5         |
Shape: 2x2
```

You can also use column indices to get a sub DataFrame:

To be continued...






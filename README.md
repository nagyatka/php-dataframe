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
>>> $df = new DataFrame([[0,1],[2,3]]);
>>> print($df);

     |0         |1         |
============================
0    |0         |1         |
1    |2         |3         |
Shape: 2x2
```

#### Create a simple DataFrame object using a 2D array, and column names:
```php
>>> $df = new DataFrame([[0,1],[2,3]], ["a", "b"]);
>>> print($df);

     |a         |b         |
============================
0    |0         |1         |
1    |2         |3         |
Shape: 2x2
```


#### Create a simple DataFrame object using a 2D array, column names, and indices:
```php
>>> $df = new DataFrame([[0,1],[2,3]], ["a", "b"], ["e", "f"]);
>>> print($df);

     |a         |b         |
============================
e    |0         |1         |
f    |2         |3         |
Shape: 2x2
```

### Get DataFrame's basic information

A DataFrame stores a 2D php array and the associated column names and indices.

#### Access to the raw 2D array
```php
>>> $df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
>>> print_r($df->values);

Array
(
    [0] => Array
        (
            [a] => 0
            [b] => 1
            [c] => 2
        )

    [1] => Array
        (
            [a] => 3
            [b] => 4
            [c] => 5
        )

)
```

#### Get shape information
```php
>>> $df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
>>> print("Number of rows: " . $df->shape[0]);
Number of rows: 2
>>> print("Number of columns: " . $df->shape[1]);
Number of columns: 3
```

#### Get/Set column names
```php
>>> print_r($df->getColumnNames());

Array
(
    [0] => a
    [1] => b
    [2] => c
)

>>> $df->setColumnNames(["x", "y", "z"]);
>>> print($df);

     |x         |y         |z         |
=======================================
e    |0         |1         |2         |
f    |3         |4         |5         |
Shape: 2x3
```

#### Get/Set indices
```php
>>> print_r($df->getIndices());

Array
(
    [0] => e
    [1] => f
)

>>> $df->setIndices(["p", "q"]);
>>> print($df);

     |x         |y         |z         |
=======================================
p    |0         |1         |2         |
q    |3         |4         |5         |
Shape: 2x3
```


### Indexing and selecting data

#### Selecting one column of a DataFrame
Selecting column `"a"`, the DataFrame object returns with a Series object.
```php
>>> $df = new DataFrame([[0,1],[2,3]], ["a", "b"], ["e", "f"]);
>>> print($df["a"]);

Series(Name=a, length=2){[
	e: 0,
	f: 2,
]}
```

Selecting a column by column's index also works
```php
>>> $df = new DataFrame([[0,1],[2,3]], ["a", "b"], ["e", "f"]);
>>> print($df[0]);

Series(Name=a, length=2){[
	e: 0,
	f: 2,
]}
```

#### Selecting multiple columns of a DataFrame
Because php does not support array object as key, the `cols` helper function needs to be used to select multi columns.
```php
>>> $df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
>>> print($df[\PHPDataFrame\cols(["b", "c"])]);

     |b         |c         |
============================
e    |1         |2         |
f    |4         |5         |
Shape: 2x2
```

You can also use column indices to get a sub DataFrame:

```php
>>> $df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
>>> print($df[\PHPDataFrame\cols([0,1])]);

     |a         |b         |
============================
e    |0         |1         |
f    |3         |4         |
Shape: 2x2
```

#### Selecting one row of a DataFrame
You can select one row of a DataFrame using `iloc` operator. 
```php
>>> $df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
>>> print($df->iloc["e"]);

Series(Index=e, Length=3){[
	a: 0,
	b: 1,
	c: 2,
]}
```
Of course, numeric indices can be used as well.
```php
>>> $df = new DataFrame([[0,1,2], [3,4,5]], ["a", "b", "c"], ["e", "f"]);
>>> print($df->iloc[0]);

Series(Index=0, Length=3){[
	a: 0,
	b: 1,
	c: 2,
]}
```


#### Selecting multiple rows of a DataFrame
Because php does not support array object as key, the `inds` helper function needs to be used to select multi columns.
```php
>>> $df = new DataFrame([[0,1,2], [3,4,5], [6,7,8]], ["a", "b", "c"], ["e", "f", "g"]);
>>> print($df->iloc[\PHPDataFrame\inds(["g","f"])]);

     |a         |b         |c         |
=======================================
g    |6         |7         |8         |
f    |3         |4         |5         |
Shape: 2x3

>>> print($df->iloc[\PHPDataFrame\inds([0,1])]);

     |a         |b         |c         |
=======================================
e    |0         |1         |2         |
f    |3         |4         |5         |
Shape: 2x3

```

Since the index values don't have to be unique, an `iloc` operation with a label index can results in multiple rows.

```php
>>> $df = new DataFrame([[0,1,2], [3,4,5], [6,7,8]], ["a", "b", "c"], ["e", "f", "e"]);
>>> print($df->iloc["e"]);

     |a         |b         |c         |
=======================================
e    |0         |1         |2         |
e    |6         |7         |8         |
Shape: 2x3
```

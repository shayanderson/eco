## Benchmark Class
The `\Eco\Benchmark` class can be used for benchmarking, for example:
```php
use Eco\Benchmark;
// start point
Benchmark::start();

usleep(50000); // do something
// print current elapsed time
pa(Benchmark::getElapsed());

// add new point
Benchmark::point('one');
usleep(50000); // do something

// add new point with debug data
Benchmark::point('two', [2 => 'two', 3 => 'three']);
usleep(100000); // do something

// stop point
Benchmark::stop();

// output benchmark totals
pa(Benchmark::getPoints());
```
Which will ouput something like:
```
Array
(
    [0] => Array
        (
            [id] => one
            [start] => 1537478380.0392
            [memory_usage] => 418.99 kb
            [memory_diff] => +376 b
            [elapsed] => 0.0504310131073
            [elapsed_point] => 0
        )

    [1] => Array
        (
            [id] => two
            [start] => 1537478380.0898
            [memory_usage] => 420.19 kb
            [memory_diff] => +1.56 kb
            [elapsed] => 0.10099911689758
            [elapsed_point] => 0.050568103790283
            [data] => Array
                (
                    [2] => two
                    [3] => test
                )

        )

)
```
The start and stop points can be outputted by using `true` as the first param in the `getPoints()` method, example:
```php
pa(Benchmark::getPoints(true));
```
Output total elapsed time:
```php
pa(Benchmark::getElapsed());
```
Output total memory usage:
```php
pa(Benchmark::getMemoryUsage());
```
Will output something like:
```
Array
(
    [start] => 418.63 kb
    [stop] => 420.27 kb
    [stop_peak] => 459.78 kb
)
```
### Print HTML Table with Benchmark Data
An HTML table with benchmark totals can be printed using something like:
```php
// after benchmarking has been completed:
echo '<style>#ecob { border-collapse:collapse; width:100%; }'
	. '#ecob th { text-align:left; background:#333; color:#fff; }'
	. '#ecob th, #ecob td { padding:3px 6px; }'
	. '#ecob td { border:1px solid #eee; }'
	. '#ecob tr:nth-child(odd) { background-color:#f2f2f2; }</style>';
echo '<table id="ecob">'
	. '<tr>'
		. '<th>Point</th>'
		. '<th>Elapsed Since Start (Seconds)</th>'
		. '<th>Since Last Point (Seconds)</th>'
		. '<th>Memory Usage</th>'
		. '<th>Memory Diff</th>'
	. '</tr>';
echo decorate('<tr>'
		. '<td>{$id}</td>'
		. '<td>{$elapsed}</td>'
		. '<td>{$elapsed_point}</td>'
		. '<td>{$memory_usage}</td>'
		. '<td>{$memory_diff}</td>'
	. '</tr>',
	Benchmark::getPoints(true));
echo '</table>';
```
--TEST--
Test compare a plain image with default config to reference
--FLAKY--
--DESCRIPTION--
Use the `./compare_image.php` script to calculate the difference
 of an existing comparison image and a newly generated one.
This test might sometimes fail due to minor differences between versions.
--CREDITS--
machinateur <hello@machinateur.dev>
--SKIPIF--
<?php include 'tests/Result/skipif.inc';
--ARGS--
tests/Result/res/default_image.png tests/Result/res/default_image_compare.png tests/Result/res/default_template.html 10
--FILE_EXTERNAL--
./compare_image.php
--EXPECTF--
matching within tolerance (%f%%)
--CLEAN--
<?php \unlink('tests/Result/res/default_image_compare.png');

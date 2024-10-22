--TEST--
Test compare a plain document with default config to reference
--FLAKY--
--DESCRIPTION--
Use the `./compare_document.php` script to calculate the difference
 of an existing comparison document and a newly generated one.
This test might sometimes fail due to minor differences between versions.
--CREDITS--
machinateur <hello@machinateur.dev>
--SKIPIF--
<?php include 'tests/Result/skipif.inc';
--ARGS--
tests/Result/res/default_document.pdf tests/Result/res/default_document_compare.pdf tests/Result/res/default_template.html 10
--FILE_EXTERNAL--
./compare_document.php
--EXPECTF--
matching within tolerance (%f%%)
--CLEAN--
<?php \unlink('tests/Result/res/default_document_compare.pdf');

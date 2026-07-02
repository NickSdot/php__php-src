--TEST--
Interface constants named namespace are reserved for ::namespace
--FILE--
<?php

interface I
{
    public const string NAMESPACE = 'namespace';
}

?>
--EXPECTF--
Fatal error: A class constant must not be called 'namespace'; it is reserved for class namespace fetching in %s on line %d

--TEST--
Enum cases named namespace are reserved for ::namespace
--FILE--
<?php

enum E {
    case NAMESPACE;
}

?>
--EXPECTF--
Fatal error: A class constant must not be called 'namespace'; it is reserved for class namespace fetching in %s on line %d

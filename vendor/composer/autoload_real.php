<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitfb1bf31ca805e5509292b6fc2f3f5ac2
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitfb1bf31ca805e5509292b6fc2f3f5ac2', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitfb1bf31ca805e5509292b6fc2f3f5ac2', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitfb1bf31ca805e5509292b6fc2f3f5ac2::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}

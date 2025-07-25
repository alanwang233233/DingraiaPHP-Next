<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit122db3f3af647d469d07097c5a127bfa
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'Plugin\\' => 7,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'A' => 
        array (
            'App\\Route\\' => 10,
            'App\\Models\\Database\\' => 20,
            'App\\Models\\Cache\\' => 17,
            'App\\Models\\' => 11,
            'App\\Middleware\\' => 15,
            'App\\Dingraia\\' => 13,
            'App\\Controller\\' => 15,
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/src',
        ),
        'Plugin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Plugin',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'App\\Route\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Route',
        ),
        'App\\Models\\Database\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Models/Database',
        ),
        'App\\Models\\Cache\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Models/Cache',
        ),
        'App\\Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Models',
        ),
        'App\\Middleware\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Middleware',
        ),
        'App\\Dingraia\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Dingraia',
        ),
        'App\\Controller\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App/Controller',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/App',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit122db3f3af647d469d07097c5a127bfa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit122db3f3af647d469d07097c5a127bfa::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit122db3f3af647d469d07097c5a127bfa::$classMap;

        }, null, ClassLoader::class);
    }
}

<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4e4f730493ac8e3bc2de452e16c92c2d
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPackio\\' => 8,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
            'CP_Groups\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPackio\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpackio/enqueue/inc',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
        'CP_Groups\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WP_Async_Request' => __DIR__ . '/..' . '/deliciousbrains/wp-background-processing/classes/wp-async-request.php',
        'WP_Background_Process' => __DIR__ . '/..' . '/deliciousbrains/wp-background-processing/classes/wp-background-process.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4e4f730493ac8e3bc2de452e16c92c2d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4e4f730493ac8e3bc2de452e16c92c2d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4e4f730493ac8e3bc2de452e16c92c2d::$classMap;

        }, null, ClassLoader::class);
    }
}

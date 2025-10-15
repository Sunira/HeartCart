<?php
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase {
    public function test_constants_defined() {
        $this->assertTrue( defined( 'WLB_VERSION' ) );
        $this->assertTrue( defined( 'WLB_PLUGIN_FILE' ) );
    }
}

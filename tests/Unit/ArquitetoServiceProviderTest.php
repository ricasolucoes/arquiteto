<?php

namespace Arquiteto\Tests\Unit;

use Arquiteto\ArquitetoProvider;
use Arquiteto\Tests\TestCase;

class ArquitetoServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_arquiteto_singleton()
    {
        $this->assertTrue($this->app->bound('arquiteto'));
    }

    /** @test */
    public function it_provides_arquiteto_service()
    {
        $provider = new ArquitetoProvider($this->app);

        $this->assertContains('arquiteto', $provider->provides());
    }

    /** @test */
    public function it_can_resolve_arquiteto_from_container()
    {
        $arquiteto = $this->app->make('arquiteto');

        $this->assertInstanceOf(\Arquiteto\Arquiteto::class, $arquiteto);
    }
}

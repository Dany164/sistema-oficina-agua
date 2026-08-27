<?php

namespace Tests\Unit;

use App\Models\ClienteModel;
use PHPUnit\Framework\TestCase;

final class ClienteModelTest extends TestCase
{
    public function test_normaliza_telefonos_de_guatemala(): void
    {
        $model = new ClienteModel();

        $this->assertSame('4545-6789', $model->normalizeTelefono('4545 6789'));
        $this->assertSame('+502 4545-6789', $model->normalizeTelefono('+502 4545 6789'));
        $this->assertSame('1234-5678', $model->normalizeTelefono('(1234) 5678'));
        $this->assertSame('', $model->normalizeTelefono(''));
    }

    public function test_validacion_de_telefono(): void
    {
        $model = new ClienteModel();

        $this->assertTrue($model->isValidTelefono('4545-6789'));
        $this->assertTrue($model->isValidTelefono('+502 4545-6789'));
        $this->assertFalse($model->isValidTelefono('abc'));
    }
}

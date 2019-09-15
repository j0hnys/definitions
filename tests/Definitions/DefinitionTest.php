<?php 

use PHPUnit\Framework\TestCase;

use j0hnys\Definitions\Tests\Sandbox\TestDefinition;

final class DefinitionTest extends TestCase
{
    public function testCheck(): void
    {
        //Arrange
        $test_definition = new TestDefinition();
        $data = [
            'database' => [
                'factories' => [
                    'Models' => 1,
                ],
                'generated_migrations' => 'some',
                'generated_model_exports' => 'thing',
                'generated_models' => 'else',
            ]
        ];

        //Act
        $result = $test_definition->check($data);

        //Assert
        $this->assertNull($result);
    }

    public function testCheckPath(): void
    {
        //Arrange
        $test_definition = new TestDefinition();
        $path = 'database/factories/Models/*';

        //Act
        $result = $test_definition->checkPath($path);

        //Assert
        $this->assertNull($result);
    }
    
}


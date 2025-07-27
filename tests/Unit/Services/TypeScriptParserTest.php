<?php 

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class TypeScriptParserTest extends TestCase {

    /** @test */
    public function itShouldGetExportedClasses() {

        // Given that we have a TypeScript parser service
        $parser = new \Kenjiefx\Pluncext\Services\TypeScriptParser();

        $tsContent = <<<TS
        export class MyClass1 {
            constructor() {}
            someFunctionWithexportclass() {}
        }
        export class MyClass2 {
            constructor() {}
            exportMyClass1() {}
        }
        export class MyClass3 {
            constructor() {}
        }
        class NotExportedClass {
            constructor() {}
        }
        export function someFunction() {}
        export const someVariable = 42;
        TS;

        // When we parse TypeScript content
        $exportedClasses = $parser->getExportedClasses($tsContent);

        // Then we should get an array of exported class names
        $this->assertCount(3, $exportedClasses);
        $this->assertContains('MyClass1', $exportedClasses);
        $this->assertContains('MyClass2', $exportedClasses);
        $this->assertContains('MyClass3', $exportedClasses);
    }

    /** @test */
    public function itShouldExtractImportStatements() {

        // Given that we have a TypeScript parser service
        $parser = new \Kenjiefx\Pluncext\Services\TypeScriptParser();

        $tsContent = <<<TS
        import { MyClass1, MyClass2 } from './my-module';
        import MyClass3 from './another-module';
        import './side-effect-module';
        import { MyClass4 as AliasClass } from './alias-module';
        TS;

        // When we extract import statements
        $importStatements = $parser->extractImportStatements($tsContent);

        // Then we should get an array of unique import statements
        $this->assertCount(4, $importStatements);
        $this->assertContains("import { MyClass1, MyClass2 } from './my-module';", $importStatements);
        $this->assertContains("import MyClass3 from './another-module';", $importStatements);
        $this->assertContains("import './side-effect-module';", $importStatements);
        $this->assertContains("import { MyClass4 as AliasClass } from './alias-module';", $importStatements);
    }

}
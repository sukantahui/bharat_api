<?php

namespace Tests\Unit;

use App\Services\SqlToSeederConverter;
use PHPUnit\Framework\TestCase;

class SqlToSeederConverterTest extends TestCase
{
    private SqlToSeederConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new SqlToSeederConverter();
    }

    public function test_parses_simple_insert_statement(): void
    {
        $sql = "INSERT INTO `users` (`id`, `name`, `email`) VALUES (1, 'Alice', 'alice@example.com'), (2, 'Bob', 'bob@example.com');";
        $result = $this->converter->parseSql($sql);

        $this->assertArrayHasKey('users', $result);
        $this->assertEquals(['id', 'name', 'email'], $result['users']['columns']);
        $this->assertCount(2, $result['users']['rows']);
        $this->assertEquals([1, 'Alice', 'alice@example.com'], $result['users']['rows'][0]);
        $this->assertEquals([2, 'Bob', 'bob@example.com'], $result['users']['rows'][1]);
    }

    public function test_handles_null_numbers_escaped_quotes_and_newlines(): void
    {
        $sql = <<<SQL
INSERT INTO `products` (`id`, `title`, `price`, `description`, `is_active`, `category_id`) VALUES
(101, 'O\'Reilly Book', 49.99, 'Line 1\\nLine 2 with \\'quotes\\' and ''escaped''', TRUE, NULL),
(102, 'Laptop "Pro"', 1299, NULL, FALSE, 5);
SQL;

        $result = $this->converter->parseSql($sql);

        $this->assertArrayHasKey('products', $result);
        $rows = $result['products']['rows'];

        $this->assertEquals(101, $rows[0][0]);
        $this->assertEquals("O'Reilly Book", $rows[0][1]);
        $this->assertEquals(49.99, $rows[0][2]);
        $this->assertEquals("Line 1\nLine 2 with 'quotes' and 'escaped'", $rows[0][3]);
        $this->assertTrue($rows[0][4]);
        $this->assertNull($rows[0][5]);

        $this->assertEquals(102, $rows[1][0]);
        $this->assertEquals('Laptop "Pro"', $rows[1][1]);
        $this->assertEquals(1299, $rows[1][2]);
        $this->assertNull($rows[1][3]);
        $this->assertFalse($rows[1][4]);
        $this->assertEquals(5, $rows[1][5]);
    }

    public function test_filters_sql_comments_and_ddl_statements(): void
    {
        $sql = <<<SQL
-- MySQL dump 10.13
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

LOCK TABLES `roles` WRITE;
INSERT INTO `roles` (`id`, `name`) VALUES (1, 'Admin'), (2, 'Editor');
UNLOCK TABLES;
SQL;

        $result = $this->converter->parseSql($sql);

        $this->assertArrayHasKey('roles', $result);
        $this->assertCount(2, $result['roles']['rows']);
        $this->assertEquals(['id', 'name'], $result['roles']['columns']);
    }

    public function test_generates_valid_php_seeder_code_with_chunks(): void
    {
        $tablesData = [
            'roles' => [
                'columns' => ['id', 'name'],
                'rows' => [
                    [1, 'Admin'],
                    [2, 'Manager'],
                    [3, 'Worker'],
                ],
            ],
        ];

        $code = $this->converter->generateSeederCode('RoleSeeder', $tablesData, [
            'mode' => 'insert',
            'chunk' => 2,
            'truncate' => true,
            'disable_fk' => true,
        ]);

        $this->assertStringContainsString('class RoleSeeder extends Seeder', $code);
        $this->assertStringContainsString('Schema::disableForeignKeyConstraints();', $code);
        $this->assertStringContainsString("DB::table('roles')->truncate();", $code);
        $this->assertStringContainsString("DB::table('roles')->insert([", $code);
        $this->assertStringContainsString('Schema::enableForeignKeyConstraints();', $code);

        // Verify PHP syntax
        $tempFile = tempnam(sys_get_temp_dir(), 'seeder_test_') . '.php';
        file_put_contents($tempFile, $code);
        exec("php -l " . escapeshellarg($tempFile), $output, $returnVar);
        unlink($tempFile);

        $this->assertEquals(0, $returnVar, "Generated Seeder PHP code has syntax errors: " . implode("\n", $output));
    }

    public function test_generates_raw_sql_seeder_mode(): void
    {
        $sql = "INSERT INTO `tags` (`id`, `name`) VALUES (1, 'Laravel'), (2, 'PHP');";
        $tablesData = $this->converter->parseSql($sql);

        $code = $this->converter->generateSeederCode('TagSeeder', $tablesData, [
            'mode' => 'raw',
            'raw_sql' => $sql,
        ]);

        $this->assertStringContainsString('class TagSeeder extends Seeder', $code);
        $this->assertStringContainsString('DB::unprepared($sql);', $code);
        $this->assertStringContainsString("INSERT INTO `tags`", $code);
    }
}

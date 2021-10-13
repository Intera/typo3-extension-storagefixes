<?php

declare(strict_types=1);

namespace Int\StorageFixes\Tests\Unit\CollectionConsistency;

use Int\StorageFixes\CollectionConsistency\PrefixReplacer;
use Int\StorageFixes\Tests\Unit\AbstractUnitTest;
use InvalidArgumentException;

final class PrefixReplacerTest extends AbstractUnitTest
{
    /**
     * @var PrefixReplacer
     */
    private $prefixReplacer;

    protected function setUp()
    {
        $this->prefixReplacer = new PrefixReplacer();
    }

    /**
     * @test
     */
    public function nonMatchingPrefixThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->prefixReplacer->replacePrefix('/some/other/prefix', '/what/prefix', '/new/prefix');
    }

    /**
     * @test
     * @dataProvider prefixIsReplacedCorrectlyDataProvider
     * @param string $prefixedString
     * @param string $oldPrefix
     * @param string $newPrefix
     * @param string $expectedResult
     */
    public function prefixIsReplacedCorrectly(
        string $prefixedString,
        string $oldPrefix,
        string $newPrefix,
        string $expectedResult
    ) {
        $result = $this->prefixReplacer->replacePrefix($prefixedString, $oldPrefix, $newPrefix);
        $this->assertEquals($expectedResult, $result);
    }

    public function prefixIsReplacedCorrectlyDataProvider(): array
    {
        return [
            'new prefix is longer' => [
                '/my/prefix/with/more',
                '/my/prefix',
                '/longer/prefix',
                '/longer/prefix/with/more',
            ],
            'new prefix is shorter' => [
                '/my/long/old/prefix/with/more',
                '/my/long/old/prefix',
                '/shorter/prefix',
                '/shorter/prefix/with/more',
            ],
            'new prefix has equal length' => [
                '/my/equal/length/prefix1/with/more',
                '/my/equal/length/prefix1',
                '/my/equal/length/prefix2',
                '/my/equal/length/prefix2/with/more',
            ],
        ];
    }
}

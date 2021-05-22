<?php
declare(strict_types=1);

namespace Danger\Tests\Struct;

use Danger\Struct\Commit;
use Danger\Struct\FileCollection;
use Danger\Struct\Github\File;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FileCollectionTest extends TestCase
{
    public function testFileExistsHelper(): void
    {
        $f = new File('');
        $f->name = 'CHANGELOG.md';
        $f->status = File::STATUS_ADDED;

        $c = new FileCollection([$f]);

        static::assertTrue($c->hasAddedFile('CHANGELOG.md'));
        static::assertFalse($c->hasModifiedFile('CHANGELOG.md'));
        static::assertFalse($c->hasRemovedFile('CHANGELOG.md'));

        $f->status = File::STATUS_MODIFIED;

        static::assertFalse($c->hasAddedFile('CHANGELOG.md'));
        static::assertTrue($c->hasModifiedFile('CHANGELOG.md'));
        static::assertFalse($c->hasRemovedFile('CHANGELOG.md'));

        $f->status = File::STATUS_REMOVED;

        static::assertFalse($c->hasAddedFile('CHANGELOG.md'));
        static::assertFalse($c->hasModifiedFile('CHANGELOG.md'));
        static::assertTrue($c->hasRemovedFile('CHANGELOG.md'));
    }

    public function testClear(): void
    {
        $f = new File('');
        $f->name = 'CHANGELOG.md';
        $f->status = File::STATUS_ADDED;

        $c = new FileCollection([$f]);

        static::assertSame(1, $c->count());
        $c->clear();
        static::assertSame(0, $c->count());
    }

    public function testAddWrongClass(): void
    {
        static::expectException(\InvalidArgumentException::class);

        new FileCollection([new Commit()]);
    }

    public function testGet(): void
    {
        $f = new File('');
        $c = new FileCollection([$f]);
        static::assertSame($f, $c->get('0'));
        static::assertNull($c->get('1'));
        static::assertCount(1, $c->getKeys());
        static::assertCount(1, $c->getElements());
        $c->remove(0);
        static::assertCount(0, $c->getElements());
    }

    public function testSlice(): void
    {
        $f = new File('');
        $c = new FileCollection([$f]);

        static::assertCount(0, $c->slice(1));
        static::assertCount(1, $c->slice(0));
    }

    public function testReduce(): void
    {
        $f = new File('');
        $c = new FileCollection([$f]);

        $bool = $c->reduce(function () {
            return true;
        });

        static::assertTrue($bool);
    }

    public function testSort(): void
    {
        $f1 = new File('');
        $f1->name = 'A';

        $f2 = new File('');
        $f2->name = 'Z';

        $c = new FileCollection([$f2, $f1]);

        $c->sort(function (File $a, File $b) {
            return $a->name <=> $b->name;
        });

        static::assertSame('A', $c->first()->name);
    }
}
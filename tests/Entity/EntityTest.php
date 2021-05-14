<?php

namespace Bookboon\Api\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Class EntityTest
 * @package Entity
 * @group entity
 */
class EntityTest extends TestCase
{
    /*
     * UUID
     */
    public function testInvalidUuid() : void
    {
        $uuid = '4343f-4343-4343';
        self::assertFalse(Entity::isValidUUID($uuid));
    }

    public function testValidUuid() : void
    {
        $uuid = 'db98ac1b-435f-456b-9bdd-a2ba00d41a58';
        self::assertTrue(Entity::isValidUUID($uuid));
    }
}
<?php

test('route ping termuat lewat ModuleServiceProvider dan memakai envelope', function () {
    $this->getJson('/api/v1/ping')
        ->assertOk()
        ->assertExactJson([
            'success' => true,
            'data' => ['pong' => true],
            'message' => 'Berhasil',
        ]);
});

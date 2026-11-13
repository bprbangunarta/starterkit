<?php

use App\Actions\Storage\AssertHostAllowed;

test('a public address may be used', function () {
    expect(app(AssertHostAllowed::class)->refusal('93.184.216.34'))->toBeNull();
});

test('internal addresses are refused unless the host was listed on purpose', function (string $host) {
    $guard = app(AssertHostAllowed::class);

    expect($guard->refusal($host))->toContain('Alamat internal tidak diizinkan');

    config(['storage_connections.allowed_hosts' => [$host]]);

    expect($guard->refusal($host))->toBeNull();
})->with(['10.0.0.5', '192.168.1.10', '172.16.4.2', '127.0.0.1', '::1', 'localhost']);

test('the cloud metadata address is refused even when it was listed', function (string $host) {
    config(['storage_connections.allowed_hosts' => [strtolower($host)]]);

    expect(app(AssertHostAllowed::class)->refusal($host))->toBe('Alamat layanan metadata tidak boleh dipakai.');
})->with(['169.254.169.254', 'fd00:ec2::254', '64:ff9b::a9fe:a9fe', '0064:ff9b::a9fe:a9fe', 'FD00:0EC2::254', '::ffff:169.254.169.254']);

test('a name that does not resolve is refused', function () {
    expect(app(AssertHostAllowed::class)->refusal('belum-ada.invalid'))->toBe('Alamat host tidak ditemukan.');
});

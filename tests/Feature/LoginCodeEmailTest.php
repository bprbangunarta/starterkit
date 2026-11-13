<?php

test('the one-time code email shows the code, its lifetime, and the warning in HTML and in text', function () {
    $data = ['code' => '482915', 'minutes' => 10, 'name' => 'Rina Wulandari', 'sentAt' => '1 Oktober 2026, 21:16 WIB'];

    $html = view('emails.login-code', $data)->render();
    $text = view('emails.login-code-text', $data)->render();

    expect($html)->toContain('482915', '10 menit', 'Rina Wulandari')
        ->and($text)->toContain('482915', 'Kode berlaku 10 menit', 'JANGAN BAGIKAN KODE INI', 'Halo Rina Wulandari,');
});

test('the greeting has no name when the person has none, and a name with markup is escaped in the HTML', function () {
    $base = ['code' => '111222', 'minutes' => 5, 'sentAt' => 'sekarang'];

    expect(view('emails.login-code-text', [...$base, 'name' => null])->render())->toContain('Halo,')
        ->and(view('emails.login-code', [...$base, 'name' => '<b>Rina</b>'])->render())->toContain('&lt;b&gt;Rina&lt;/b&gt;')->not->toContain('<b>Rina</b>');
});


test('md5', () => {
    expect(md5('fortuna')).toBe('39e3c3d3cbf9064e35f1bee7dbd176f8');
});

test('saltos.proxy.human_size', () => {
    expect(saltos.proxy.human_size(1073741824, ' ', 'bytes')).toBe('1 Gbytes');
    expect(saltos.proxy.human_size(1073741823, ' ', 'bytes')).toBe('1024 Mbytes');
    expect(saltos.proxy.human_size(1048576, ' ', 'bytes')).toBe('1 Mbytes');
    expect(saltos.proxy.human_size(1048575, ' ', 'bytes')).toBe('1024 Kbytes');
    expect(saltos.proxy.human_size(1024, ' ', 'bytes')).toBe('1 Kbytes');
    expect(saltos.proxy.human_size(1023, ' ', 'bytes')).toBe('1023 bytes');

    expect(saltos.proxy.human_size(1073741824, ' ')).toBe('1 G');
    expect(saltos.proxy.human_size(1073741823, ' ')).toBe('1024 M');
    expect(saltos.proxy.human_size(1048576, ' ')).toBe('1 M');
    expect(saltos.proxy.human_size(1048575, ' ')).toBe('1024 K');
    expect(saltos.proxy.human_size(1024, ' ')).toBe('1 K');
    expect(saltos.proxy.human_size(1023, ' ')).toBe('1023 ');

    expect(saltos.proxy.human_size(1073741824)).toBe('1G');
    expect(saltos.proxy.human_size(1073741823)).toBe('1024M');
    expect(saltos.proxy.human_size(1048576)).toBe('1M');
    expect(saltos.proxy.human_size(1048575)).toBe('1024K');
    expect(saltos.proxy.human_size(1024)).toBe('1K');
    expect(saltos.proxy.human_size(1023)).toBe('1023');
});

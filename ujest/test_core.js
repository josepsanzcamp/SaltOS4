
test('saltos.core.uniqid', () => {
    const array = [];
    for (let i = 0; i < 1000; i++) {
        array.push(saltos.core.uniqid());
    }
    expect(array.length).toBe(1000);
    const copia = new Set(array);
    expect(array.length).toBe(copia.size);
    expect(array[0].length).toBeGreaterThanOrEqual(10);
    expect(array[0].startsWith('id')).toBe(true);
    expect(typeof array[0]).toBe('string');
});

test('saltos.core.is_number', () => {
    expect(saltos.core.is_number(123)).toBe(true);
    expect(saltos.core.is_number(123.456)).toBe(true);
    expect(saltos.core.is_number('123')).toBe(true);
    expect(saltos.core.is_number('123.456')).toBe(true);
    expect(saltos.core.is_number('asd123')).toBe(false);
    expect(saltos.core.is_number('123asd')).toBe(false);
    expect(saltos.core.is_number(Infinity)).toBe(false);
    expect(saltos.core.is_number(-Infinity)).toBe(false);
    expect(saltos.core.is_number(NaN)).toBe(false);
});

test('saltos.core.human_size', () => {
    expect(saltos.core.human_size(1073741824, ' ', 'bytes')).toBe('1 Gbytes');
    expect(saltos.core.human_size(1073741823, ' ', 'bytes')).toBe('1024 Mbytes');
    expect(saltos.core.human_size(1048576, ' ', 'bytes')).toBe('1 Mbytes');
    expect(saltos.core.human_size(1048575, ' ', 'bytes')).toBe('1024 Kbytes');
    expect(saltos.core.human_size(1024, ' ', 'bytes')).toBe('1 Kbytes');
    expect(saltos.core.human_size(1023, ' ', 'bytes')).toBe('1023 bytes');

    expect(saltos.core.human_size(1073741824, ' ')).toBe('1 G');
    expect(saltos.core.human_size(1073741823, ' ')).toBe('1024 M');
    expect(saltos.core.human_size(1048576, ' ')).toBe('1 M');
    expect(saltos.core.human_size(1048575, ' ')).toBe('1024 K');
    expect(saltos.core.human_size(1024, ' ')).toBe('1 K');
    expect(saltos.core.human_size(1023, ' ')).toBe('1023 ');

    expect(saltos.core.human_size(1073741824)).toBe('1G');
    expect(saltos.core.human_size(1073741823)).toBe('1024M');
    expect(saltos.core.human_size(1048576)).toBe('1M');
    expect(saltos.core.human_size(1048575)).toBe('1024K');
    expect(saltos.core.human_size(1024)).toBe('1K');
    expect(saltos.core.human_size(1023)).toBe('1023');
});

test('saltos.core.timestamp', () => {
    const timestamp = saltos.core.timestamp();
    expect(timestamp + 1).toBeGreaterThanOrEqual(saltos.core.timestamp());
    expect(timestamp + 2).toBeGreaterThanOrEqual(saltos.core.timestamp(1));
    expect(timestamp + 61).toBeGreaterThanOrEqual(saltos.core.timestamp(60));
    expect(timestamp + 3601).toBeGreaterThanOrEqual(saltos.core.timestamp(3600));
    expect(timestamp + 86401).toBeGreaterThanOrEqual(saltos.core.timestamp(86400));
    expect(saltos.core.timestamp().toString().length).toBeGreaterThanOrEqual(10);
    expect(saltos.core.is_number(saltos.core.timestamp())).toBe(true);
});

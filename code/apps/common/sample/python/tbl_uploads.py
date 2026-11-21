import gzip

data = [
    (1, 1, '2026-11-02 09:20:15', 'idd19c1b36230', 'customers', 'contract.pdf', 184320, 'application/pdf', '1793607615_3457c24124299bb7b72f6beb44c46e70_contract.pdf', '254a3bfd6fe6007afb3dfab434dbbd28'),
    (2, 1, '2026-11-02 11:05:40', 'id0c1b8586be7', 'invoices', 'invoice_2026_0142.pdf', 96512, 'application/pdf', '1793613940_ebf3c357c61a798a0f44c8f9d46a4e76_invoice_2026_0142.pdf', 'fc49fad2c1a0ebd6e3efa7ca9c17dadb'),
    (3, 1, '2026-11-03 10:12:03', 'id5901c5aca94', 'employees', 'photo.jpg', 245760, 'image/jpeg', '1793697123_225c92d117d19652f9d2ed85ce4f8452_photo.jpg', '541ca3372ef1c535286178edeb756431'),
    (4, 1, '2026-11-03 16:48:27', 'id4d119ca5de2', 'meetings', 'minutes.docx', 38912, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '1793720907_985e8f0045b2f085e1757383fbe897f8_minutes.docx', 'fbd0b4f1bcec6d19d11426a720f0866a'),
    (5, 1, '2026-11-04 09:33:51', 'id3173c1c8c4f', 'products', 'catalog.xlsx', 71680, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '1793781231_bb91da5dca723314f2efa5d3de038ada_catalog.xlsx', 'edb5fa8b398cbf81e4df8a0469a52e8f'),
    (6, 1, '2026-11-04 12:01:09', 'idf1cf23036b1', 'emails', 'logo.png', 15360, 'image/png', '1793790069_c5c368688a843a9fdac714a0311b7d50_logo.png', '85d79cfb5d351f63fbddc86e3bfd0282'),
    (7, 1, '2026-11-05 08:47:30', 'idd99dd9e7f1b', 'quotes', 'quote_2026_0087.pdf', 88064, 'application/pdf', '1793864850_844dd130bf41ef0318e7937f5732c2f4_quote_2026_0087.pdf', 'e44cadf5a21a16efaff1e766ab3db151'),
    (8, 1, '2026-11-05 15:22:18', 'id6b9863670f5', 'leads', 'notes.txt', 2048, 'text/plain', '1793888538_ef3bde96c43a27ca2da1ab9e48f88915_notes.txt', '721a37eaca027c99a6c31ae58da4ab41'),
    (9, 1, '2026-11-06 10:10:10', 'id9afa5369cba', 'suppliers', 'price_list.csv', 12288, 'text/csv', '1793956210_b8d489584f9e228fb61136b9e0563849_price_list.csv', '01374d8056523d8dfd3a0dab18bf7ceb'),
    (10, 1, '2026-11-07 13:55:42', 'idaa1a09182dd', 'workorders', 'report.pdf', 132096, 'application/pdf', '1794056142_743a1add11802f39e76ae16e3ba71e59_report.pdf', '811cc58dac499eaa84c9cd8a148ffd74')
]

with gzip.open('tbl_uploads.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO tbl_uploads (id, user_id, datetime, uniqid, app, name, size, type, file, hash) VALUES\n")
    for i, row in enumerate(data):
        line = "({}, {}, '{}', '{}', '{}', '{}', {}, '{}', '{}', '{}')".format(
            row[0], row[1], row[2], row[3], row[4],
            row[5].replace("'", "''"),
            row[6], row[7],
            row[8].replace("'", "''"),
            row[9]
        )
        if i < len(data) - 1:
            line += ","
        f.write(line + "\n")
    f.write(";\n")

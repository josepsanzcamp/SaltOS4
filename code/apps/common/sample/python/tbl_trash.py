import gzip

data = [
    (1, 3, 1, '2026-10-12 10:15:00', 57, 50, 'idb4e0829d59d', 'offer.pdf', 152576, 'application/pdf', '1791792900_5962768938065457f0622a3ee9b7d3c8_offer.pdf', 'a2cad3e329c1333630ef0dcbb766a7b8'),
    (2, 1, 1, '2026-10-14 09:40:22', 12, 53, 'id7de361cd66f', 'invoice_2026_0098.pdf', 90112, 'application/pdf', '1791963622_96f89713609c219a494feb4bbf0e9a9a_invoice_2026_0098.pdf', 'b4a468cf995797de84be51a6d26a6216'),
    (3, 2, 1, '2026-10-15 12:05:47', 33, 58, 'iddfd0f886f08', 'cv.pdf', 204800, 'application/pdf', '1792058747_a5cbff6b3f0af4ae4929901273e60de2_cv.pdf', 'e8e9f099494f1216bf1f5ed01f62dd08'),
    (4, 1, 1, '2026-10-18 17:30:10', 8, 61, 'id90257d9028c', 'agenda.docx', 27648, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '1792337410_d12e4fb2baa93c341f6a711bdfae8401_agenda.docx', '2c0c7d853f8b4bb90867409cc57c5b39'),
    (5, 4, 1, '2026-10-20 08:55:31', 71, 54, 'idf0dbdc7e33e', 'datasheet.pdf', 311296, 'application/pdf', '1792479331_274b9ed0090ec19fe96e9ce78fe70008_datasheet.pdf', 'a1d620555e65600804c211892c4c8362'),
    (6, 2, 1, '2026-10-22 11:11:45', 45, 52, 'id83116ce2b4c', 'quote_2026_0061.pdf', 84992, 'application/pdf', '1792660305_c5f86a685b2bbb58f6d28e9e0336981f_quote_2026_0061.pdf', '689a3b498d4e3b508a7ed80b9044a70c'),
    (7, 1, 1, '2026-10-25 16:02:03', 19, 51, 'idac1c7f57c87', 'business_card.jpg', 58368, 'image/jpeg', '1792940523_24a540b7210571b434ac5825b0c18c8f_business_card.jpg', 'c22ccbcec81541ec33ff541365e90b6b'),
    (8, 3, 1, '2026-10-27 10:44:19', 64, 56, 'id80f5d81e5ff', 'old_prices.xlsx', 65536, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '1793094259_63b5d5f78b72a597c14d85758a292fab_old_prices.xlsx', '26c559a6a5680fb842ecb8a5164754fd'),
    (9, 1, 1, '2026-10-29 13:20:55', 92, 60, 'id788f6cafe8b', 'photo_before.png', 409600, 'image/png', '1793276455_c2bc8593e71f41746080ae0d160593c2_photo_before.png', 'f239f8e20a41560416a9512648f87d3e'),
    (10, 2, 1, '2026-10-31 09:09:09', 1, 73, 'idafde9a546dc', 'old_logo.png', 20480, 'image/png', '1793434149_8a82e329a4e2d18c66abe170eb89f626_old_logo.png', 'e667a744ba51bae5e8f506ac8c64e718')
]

with gzip.open('tbl_trash.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO tbl_trash (id, old_id, user_id, datetime, reg_id, app_id, uniqid, name, size, type, file, hash) VALUES\n")
    for i, row in enumerate(data):
        line = "({}, {}, {}, '{}', {}, {}, '{}', '{}', {}, '{}', '{}', '{}')".format(
            row[0], row[1], row[2], row[3], row[4], row[5], row[6],
            row[7].replace("'", "''"),
            row[8], row[9],
            row[10].replace("'", "''"),
            row[11]
        )
        if i < len(data) - 1:
            line += ","
        f.write(line + "\n")
    f.write(";\n")

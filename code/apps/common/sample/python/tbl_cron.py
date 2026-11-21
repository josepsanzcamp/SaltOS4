import gzip

indexing = 'php index.php indexing; user=admin php index.php integrity'
emails = 'user=admin php index.php app/emails/server; php index.php app/emails/indexing'
gc = 'php index.php gc'

data = [
    (1, indexing, 1201, '{"indexing_files":{"time":0.006687,"total":0},"indexing_apps":{"time":0.044765,"total":5}}{"integrity":{"time":0.024018,"total":5}}', '', '2026-11-02 09:00:00', '2026-11-02 09:00:01'),
    (2, emails, 1204, '["12 email(s) received","0 email(s) sended"]{"indexing":{"time":1.789123,"total":44}}', '', '2026-11-02 09:00:00', '2026-11-02 09:00:04'),
    (3, indexing, 1312, '{"indexing_files":{"time":0.005121,"total":2},"indexing_apps":{"time":0.031877,"total":3}}{"integrity":{"time":0.019342,"total":3}}', '', '2026-11-02 09:05:00', '2026-11-02 09:05:01'),
    (4, emails, 1315, '["3 email(s) received","1 email(s) sended"]{"indexing":{"time":0.412755,"total":4}}', '', '2026-11-02 09:05:00', '2026-11-02 09:05:02'),
    (5, emails, 1420, '["0 email(s) received","0 email(s) sended"]{"indexing":{"time":0.001204,"total":0}}', 'Could not connect to the mail server', '2026-11-02 09:10:00', '2026-11-02 09:10:30'),
    (6, gc, 2060, '{"gc_upload":{"time":0.000837,"deleted":[],"count":0},"gc_trash":{"time":0.000616,"deleted":[],"count":0},"gc_exec":{"time":0.011404,"deleted":[],"count":0}}', '', '2026-11-02 22:00:00', '2026-11-02 22:00:00'),
    (7, indexing, 2311, '{"indexing_files":{"time":0.004532,"total":0},"indexing_apps":{"time":0.022105,"total":1}}{"integrity":{"time":0.017764,"total":1}}', '', '2026-11-03 08:00:00', '2026-11-03 08:00:01'),
    (8, emails, 2314, '["7 email(s) received","2 email(s) sended"]{"indexing":{"time":0.903318,"total":9}}', '', '2026-11-03 08:00:00', '2026-11-03 08:00:03'),
    (9, gc, 3150, '{"gc_upload":{"time":0.000912,"deleted":[],"count":0},"gc_trash":{"time":0.000704,"deleted":[],"count":0},"gc_exec":{"time":0.010877,"deleted":[],"count":0}}', '', '2026-11-03 22:00:00', '2026-11-03 22:00:00'),
    (10, indexing, 3402, '{"indexing_files":{"time":0.005873,"total":1},"indexing_apps":{"time":0.028640,"total":2}}{"integrity":{"time":0.018291,"total":2}}', '', '2026-11-04 08:00:00', '2026-11-04 08:00:01')
]

with gzip.open('tbl_cron.sql.gz', 'wt', encoding='utf-8') as f:
    f.write("INSERT INTO tbl_cron (id, cmd, pid, `out`, err, `start`, `stop`) VALUES\n")
    for i, row in enumerate(data):
        line = "({}, '{}', {}, '{}', '{}', '{}', '{}')".format(
            row[0],
            row[1].replace("'", "''"),
            row[2],
            row[3].replace("'", "''"),
            row[4].replace("'", "''"),
            row[5], row[6]
        )
        if i < len(data) - 1:
            line += ","
        f.write(line + "\n")
    f.write(";\n")


const path = require('path');

module.exports = {
    resolveSnapshotPath: (testPath, snapshotExtension) =>
        path.join(path.dirname(testPath), 'snaps', path.basename(testPath) + snapshotExtension),

    resolveTestPath: (snapshotPath, snapshotExtension) =>
        snapshotPath.replace('snaps/', '').slice(0, -snapshotExtension.length),

    testPathForConsistencyCheck: 'some/__tests__/example.test.js'
};


module.exports = {
    //testEnvironment: 'node',
    testEnvironment: 'jsdom',
    setupFiles: ['<rootDir>/../ujest/lib/jest.setup.js'],
    roots: ['<rootDir>/../ujest'],
    testMatch: ['**/test_*.js'],
    collectCoverage: true,
    coverageDirectory: '/tmp/jest.report',
    coveragePathIgnorePatterns: ['/lib/'],
    snapshotResolver: '<rootDir>/../ujest/lib/snapshotResolver.js',
};

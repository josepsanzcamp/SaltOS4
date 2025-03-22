
module.exports = {
    testEnvironment: 'jsdom',
    setupFiles: ['<rootDir>/../ujest/lib/jest.setup.js'],
    roots: ['<rootDir>/../ujest'],
    testMatch: ['**/test_*.js'],
    collectCoverage: true,
    coverageReporters: ['json'],
    coverageDirectory: '/tmp/nyc_output/jest',
};

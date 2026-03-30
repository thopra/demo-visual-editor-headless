/*
    Experiment: if using a proxy api endpoint, will cookies be sent via ssr?
 */

export default defineEventHandler((event) => {
    return event.$fetch('https://api.typo3.ddev.site/?type=834')
})
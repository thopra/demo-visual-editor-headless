/*
    Experiment: if using a proxy api endpoint, will cookies be sent via ssr?
 */

export default defineEventHandler((event) => {
    console.log(parseCookies(event))
    return event.$fetch(`https://api.typo3.ddev.site/${event.context.params._}`)
})
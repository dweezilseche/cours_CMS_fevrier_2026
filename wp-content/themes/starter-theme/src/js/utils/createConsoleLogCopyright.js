/**
 * Create console.log copyright
 */

export const createConsoleLogCopyright = () => {
  const made = 'font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif; font-size: 14px; color: #ffffff; padding:10px;background-color:#000000;'
  const website = 'font-size:10px; padding: 5px; color: #ffffff;'
  
  return console.log('%cMade by Wokine%c\n' + 'https://wokine.com', made, website)
}

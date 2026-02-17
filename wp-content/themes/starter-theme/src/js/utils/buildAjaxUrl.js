/**
* Create Ajax URL
* @param {string} action action Element
* @param {params} params Obj Element
* @returns {string} an ajax url with action and params
 */

export const buildAjaxUrl = (action, params = {}) => {
  let url = `/wp-admin/admin-ajax.php?action=${action}`
  if (Object.keys(params).length) url += `&${Object.keys(params).map((key) => `${key}=${params[key]}`).join('&')}`
  return url
}

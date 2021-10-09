export function displayMessage(zone, type = 'success', content)
{
    zone.classList.add('flash--'+type);
    zone.innerHTML = content;
}
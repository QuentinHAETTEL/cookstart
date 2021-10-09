import { BASE_URL } from '../config';


export function redirectToHomepage()
{
    window.location = BASE_URL;
}


export function redirectTo(page)
{
    window.location = BASE_URL+page;
}
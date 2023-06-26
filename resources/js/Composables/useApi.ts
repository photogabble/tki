import {useCookies} from "@vueuse/integrations/useCookies";

export function useApi() {
    return {
        get: async (url: string): Promise<Response> => {
            return fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });
        },

        post: async (url: string, data: any): Promise<Response> => {
            const csrf_token = useCookies().get('XSRF-TOKEN');
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': csrf_token,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
        }
    }
}
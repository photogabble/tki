export interface PaginationLink {
    active: boolean;
    label: string;
    url: string;
}

export interface PaginatedResource <T> {
    data: Array<T>;
    links: {
        first: string,
        last: string,
        next: null | string,
        prev: null | string,
    };
    meta: {
        current_page: number,
        from: number,
        last_page: number,
        links: Array<PaginationLink>,
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}
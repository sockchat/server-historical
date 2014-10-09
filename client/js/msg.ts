class Message {
    static Separator = "\t";

    static Pack(id: number, ...params: string[]): string {
        return id + this.Separator + params.join(this.Separator);
    }

    static PackArray(arr: string[]): string {
        return arr.join(this.Separator);
    }
}
export function importStyle(
  url: string,
  data?: { [key: string]: string },
  id?: string,
  container?: HTMLElement
): Promise<Event> {
  return new Promise<Event>((res, rej) => {
    if (id && document.getElementById(id)) {
      console.warn(`[importStyle] #${id} was found, aborting import`);
      return;
    }
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.type = "text/css";
    link.href = url;
    if (!id) {
      const match = url.match(/.*\/(?<name>.*?)\./);

      if (match?.groups?.name) {
        // Guessing id from file name
        id = match.groups.name;
      } else {
        throw new Error("Can't guess id");
      }
    }
    link.id = id;
    if (!container) {
      container = document.head;
    }
    if (data) {
      for (const [key, value] of Object.entries(data)) {
        link.setAttribute(key, value);
      }
    }
    container.appendChild(link);

    link.onload = function (ev) {
      res(ev);
    };
    link.onerror = function (ev) {
      rej(ev);
    };
  });
}

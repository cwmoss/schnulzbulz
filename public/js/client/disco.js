import { El } from "./el.js";

const postid = window.location.href;

const host = "http://localhost:5001/api/";
class Api {
  async get(meth, indata) {
    const url = new URL(host + meth);
    url.search = new URLSearchParams(indata).toString();
    const resp = await fetch(url);
    const data = await resp.json();
    return data;
  }
  async post(meth, indata) {
    const resp = await fetch(host + meth, {
      method: "POST", // or 'PUT'
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(indata),
    });

    const data = await resp.json();
    return data;
  }
}

const api = new Api();

const store = El.observable({ comments: [] });

class ReplyBox extends El {
  async submit_comment() {
    console.log("++ reply", this.$refs.reply.value, this.parent);
    const resp = await api.post("reply", {
      url: postid,
      parent_id: this.parent,
      content: this.$refs.reply.value,
    });

    console.log("++ resp", resp);
    store.comments.splice(3, 0, resp.new_comment);
    // store.comments = resp.comments;
    // store.comments = [];

    const event = new CustomEvent("newreply", {
      bubbles: true,
      composed: true,
      detail: resp,
    });

    this.dispatchEvent(event);
  }
  render(html) {
    return html`
      <div class="reply-box">
        <div class="comment-edit">
          <textarea ref="reply" class="comment-input"></textarea>
          <div class="action">
            <button
              type="button"
              class="primary"
              onclick=${this.submit_comment}
            >
              Submit
            </button>
          </div>
        </div>
      </div>
    `;
  }
  styles(css) {
    return css`
      .reply-box {
        padding: 1em;
      }
      button {
        margin-top: 5px;
        margin-right: 5px;
        padding: 5px 10px;
        border-radius: 3px;
        font-weight: 600;
        border-style: none;
        background: #fff;
        box-shadow: rgba(3, 8, 20, 0.3) 0px 0.15rem 0.1rem,
          rgba(3, 8, 20, 0.3) 0.15rem 0 0.1rem;
        display: inline;
        transition: all 200ms;
      }
      button.primary {
        background-color: #51c205;
        color: white;
      }
      textarea {
        width: 100%;
      }
    `;
  }
}

class Comment extends El {
  created() {
    this.state = this.$observable({ open: false });
  }
  show_reply_box() {}
  render(html) {
    return html`
      <div class="comment-display">
        <div class="avatar"></div>
        <div class="comment">
          <div class="meta">
            <strong>@${this.item.id}</strong>
            <span class="date">${this.item.created_at}</span>
          </div>
          <div class="content">${this.item.content}</div>
          <div class="action">
            <button class="reply-btn" onclick=${() => (this.state.open = true)}>
              Reply
            </button>
          </div>
        </div>
        ${this.state.open
          ? html`<div class="reply">
              <the-reply-box parent=${this.item.id}></the-reply-box>
            </div>`
          : ""}
      </div>
    `;
  }
  styles(css) {
    return css`
      .comment-display {
        display: flex;
        flex-wrap: wrap;
        padding: 1em;
        font-family: Roboto, Arial, sans-serif;
      }
      .meta {
        font-size: 12px;
        margin-bottom: 0.5em;
      }
      .date {
        color: #666;
      }
      .content {
        font-size: 14px;
        line-height: 1.4;
      }
      .action button {
        background-color: white;
        font-weight: 500;
        border: none;
        border: 1px solid #fff;
        border-radius: 12px;
        padding: 4px 8px;
      }
      .action button:hover {
        background-color: #ddd;
        border: 1px solid #ddd;
      }
      .comment {
        width: 90%;
      }
      .reply {
        flex-shrink: 0;
        width: 100%;
      }
      .avatar {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        border: 0px solid gray;
        border-radius: 50%;
        background-color: #eee;
        margin-right: 1em;
      }
    `;
  }
}

class Disco extends El {
  async created() {
    this.state = this.$observable({ total: 0, csize: false, comments: [] });

    const data = await api.post("comments", { url: postid });
    console.log("data+++ ", data);
    this.state.total = data.total;
    store.comments = data.comments;
  }
  mounted() {
    this.box = this.shadowRoot.querySelector(".comment-box");
    this.shadowRoot.addEventListener("newreply", (e) =>
      console.log("+++ event catched", e.detail, this.state.count)
    );
  }
  increment() {
    this.state.count += 1;
  }

  async submit_comment() {
    // const text = this.box.querySelector("#c-top").value;
    const text = this.$refs.tl_comment.value;
    console.log("+++ submit", text, window.document.title);
    if (text) {
      const resp = await api.post("new_comment", {
        url: postid,
        content: text,
        title: window.document.title,
      });
      console.log("++ resp", resp);
      this.state.total = resp.total;
      store.comments = resp.comments;
      this.$refs.tl_comment.value = "";
      this.state.csize = false;
    }
  }
  enter_comment() {
    console.log("+++ im feld");
    this.state.csize = true;
  }
  leave_comment() {
    console.log("+++ aus feld");
    this.state.csize = false;
  }
  xxrender(html) {
    return html`
      <span>Count: ${this.state.count}</span>
      <button onclick=${this.increment}>Increment</button>
    `;
  }

  render(html) {
    return html`
      <div class="comment-box">
        <div class="header">
          <span class="xlogo"
            ><svg
              xmlns="http://www.w3.org/2000/svg"
              height="24"
              viewBox="0 -960 960 960"
              width="24"
            >
              <path
                d="M906-55 746-215H149q-39.05 0-66.525-27.475Q55-269.95 55-309v-502q0-39.463 27.475-67.231Q109.95-906 149-906h662q39.463 0 67.231 27.769Q906-850.463 906-811v756ZM149-309v-502 502Zm623 0 39 46v-548H149v502h623Z"
              /></svg
          ></span>
          <span>${this.state.total} Comments</span>
        </div>
        <div class="comment-edit">
          <textarea
            ref="tl_comment"
            onfocus=${this.enter_comment}
            class="comment-input ${this.state.csize && "max"}"
            placeholder="Join the disco..."
          ></textarea>
          <div class="action">
            <button
              type="button"
              class="primary"
              onclick=${this.submit_comment}
            >
              Submit
            </button>
          </div>
        </div>
        <div class="comments">
          ${store.comments.map(
            (item) =>
              html`<the-comment item=${item} key=${item.id}></the-comment>`
          )}
        </div>
      </div>
    `;
  }

  styles(css) {
    return css`
      *,
      *::before,
      *::after {
        box-sizing: border-box;
      }
      :host {
        --ff-1: Helvetica, Arial sans-serif;
      }
      .comment-box {
        font-family: var(--ff-1);
      }
      .header {
        border-bottom: 2px solid black;
        margin-bottom: 1em;
        padding-bottom: 0.5em;
      }
      .comment-display {
        display: flex;
      }
      .comments {
        background-color: white;
        margin-top: 1em;
      }
      .comment-box {
        margin-top: 20px;
        border-radius: 2px;
        padding: 20px;
        color: #333;
        box-sizing: border-box;
        box-shadow: rgba(3, 8, 20, 0.2) 0px 0.15rem 0.1rem,
          rgba(3, 8, 20, 0.2) 0px 0.075rem 0.1rem;
        background-color: #f3f3f3;
      }
      .comment-edit,
      .comment-edit textarea {
        width: 100%;
      }
      .comment-edit textarea.max {
        height: 8em;
      }
      .comment-box .comment {
        border-radius: 5px;
        padding: 20px;
        background-color: #fff;
        box-shadow: rgba(3, 8, 20, 0.1) 0px 0.15rem 0.5rem,
          rgba(2, 8, 20, 0.1) 0px 0.075rem 0.175rem;
      }

      .comment-box .author,
      .likes {
        color: #777;
        font-weight: bold;
        font-size: 14px;
      }
      .comment-box .author {
        margin-top: 10px;
      }

      .comment-box .likes {
        margin-bottom: 10px;
      }

      .comment-box .reply-box {
        margin-top: 10px;
      }

      .comment-box input {
        padding: 5px 10px;
        margin-right: 5px;
        border-style: solid;
        border-radius: 5px;
        border-width: 1px;
        border-color: #777;
        box-shadow: rgba(3, 8, 20, 0.1) 0px 0.15rem 0.5rem,
          rgba(2, 8, 20, 0.1) 0px 0.075rem 0.175rem;
      }

      .comment-box button {
        margin-top: 5px;
        margin-right: 5px;
        padding: 5px 10px;
        border-radius: 3px;
        font-weight: 600;
        border-style: none;
        background: #fff;
        box-shadow: rgba(3, 8, 20, 0.3) 0px 0.15rem 0.1rem,
          rgba(3, 8, 20, 0.3) 0.15rem 0 0.1rem;
        display: inline;
        transition: all 200ms;
      }
      button.primary {
        background-color: #51c205;
        color: white;
      }
      .comment-box button:hover {
        background: #eee;
      }

      .comment-box button:active {
        background: #ddd;
      }
    `;
  }
}

customElements.define("the-reply-box", ReplyBox);

customElements.define("the-comment", Comment);

customElements.define("the-disco", Disco);

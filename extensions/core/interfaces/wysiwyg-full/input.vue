<template>
    <div class="interface-wysiwyg-container editor"
         :id="name"
         :name="name"
         @input="$emit('input', $event.target.innerHTML)"
    >
        <editor-menu-bar :editor="editor">
            <div class="menubar" slot-scope="{ commands, isActive }">

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.bold() }"
                        @click="commands.bold"
                >
                    <icon name="format_bold"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.italic() }"
                        @click="commands.italic"
                >
                    <icon name="format_italic"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.strike() }"
                        @click="commands.strike"
                >
                    <icon name="format_strikethrough"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.underline() }"
                        @click="commands.underline"
                >
                    <icon name="format_underline"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.code() }"
                        @click="commands.code"
                >
                    <icon name="code"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.paragraph() }"
                        @click="commands.paragraph"
                >
                    <icon name="subject"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.heading({ level: 1 }) }"
                        @click="commands.heading({ level: 1 })"
                >
                    <span>H1</span>
                    <icon name="crop_square"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.heading({ level: 2 }) }"
                        @click="commands.heading({ level: 2 })"
                >
                    <span>H2</span>
                    <icon name="crop_square"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.heading({ level: 3 }) }"
                        @click="commands.heading({ level: 3 })"
                >
                    <span>H3</span>
                    <icon name="crop_square"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.bullet_list() }"
                        @click="commands.bullet_list"
                >
                    <icon name="format_list_bulleted"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.ordered_list() }"
                        @click="commands.ordered_list"
                >
                    <icon name="format_list_numbered"/>
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.blockquote() }"
                        @click="commands.blockquote"
                >
                    <icon name="format_quote"/>
                    s
                </button>

                <button
                        class="menubar__button"
                        :class="{ 'is-active': isActive.code_block() }"
                        @click="commands.code_block"
                >
                    <icon name="code"/>
                </button>

                <button
                        class="menubar__button"
                        @click="commands.horizontal_rule"
                >
                    <icon name="hr"/>
                </button>

            </div>
        </editor-menu-bar>

        <!--<template>-->
        <!--<FileUpload type="file" name="editor" class="select"></FileUpload>-->
        <!--</template>-->
        <editor-content ref="editor" :class="['interface-wysiwyg', (readonly ? 'readonly' : '')]"
                        class="editor__content" :editor="editor"/>

    </div>
</template>

<script>

    import FileUpload from '../file/input'
    import Icon from './Components/Icon'
    import {Editor, EditorContent, EditorMenuBar} from 'tiptap'
    import {
        Blockquote,
        CodeBlock,
        HardBreak,
        Heading,
        Image,
        HorizontalRule,
        OrderedList,
        BulletList,
        ListItem,
        Table,
        TableHeader,
        TableCell,
        TableRow,
        TodoItem,
        TodoList,
        Bold,
        Code,
        Italic,
        Link,
        Strike,
        Underline,
        History,
    } from 'tiptap-extensions'

    import mixin from "../../../mixins/interface";

    export default {
        name: "interface-wysiwyg",
        mixins: [mixin],
        watch: {
            value(newVal) {
                if (newVal) {
                    console.log(this.editor.view.dom.innerHTML)

                    console.log(this)
                    console.log(this.value)
                    //return this.value.target.innerHTML
                }
            },

            methods: {

                showImagePrompt(command) {
                    this.fileUpload = !this.fileUpload
                    let src = prompt(src);
                    console.log(FileUpload)
                    if (src !== null) {
                        command({src})
                    }
                },

                init() {
                    this.editor = new Editor({
                        extensions: [
                            new Blockquote(),
                            new BulletList(),
                            new CodeBlock(),
                            new Image(),
                            new HardBreak(),
                            new Heading({levels: [1, 2, 3]}),
                            new HorizontalRule(),
                            new ListItem(),
                            new OrderedList(),
                            new Table(),
                            new TableRow(),
                            new TableHeader(),
                            new TableCell(),
                            new TodoItem(),
                            new TodoList(),
                            new Bold(),
                            new Code(),
                            new Italic(),
                            new Link(),
                            new Strike(),
                            new Underline(),
                            new History(),
                        ],
                        content: "",
                    });

                    if (this.value) {
                        this.editor.setContent(this.value);
                    }
                },
            },

            components: {
                EditorContent,
                EditorMenuBar,
                Icon
            },

            data() {
                return {
                    editor: null,
                }
            },

            mounted() {
                this.init();
            },
            beforeDestroy() {
                this.editor.destroy()
            },
        }
    }
</script>

<style lang="scss">
    .editor {
        .menubar__button {
            position: relative;
            span, i {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
            }

            span {
                top: calc(50% + 1px);
                left: calc(50% + 4px);
                font-size: 8px;
                letter-spacing: -1px;
            }
        }

        .tableWrapper {
            max-width: 100%;
            overflow-x: auto;
            table {

                background-color: solid var(--lightest-gray) 1px;
                border: solid var(--gray) 1px;
                width: 100%;
                tbody {

                    tr, td {
                        min-width: 70px;
                        border: 1px solid black;
                    }
                }
            }
        }
    }

</style>

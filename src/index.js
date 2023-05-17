import "./index.scss"
import { useSelect } from "@wordpress/data"
import { useState, useEffect } from "react";
import apiFetch from "@wordpress/api-fetch";

const __ = wp.i18n.__;

wp.blocks.registerBlockType("ourplugin/featured-professor", {
    title: "Professor Callout",
    description: "Include a short description and link to a professor of your choice",
    icon: "welcome-learn-more",
    category: "common",
    attributes: {
        profId: { type: "string" }
    },
    edit: EditComponent,
    save: function () {
        return null
    }
})

function EditComponent(props) {

    const [thePreview, setThePreview] = useState("");

    // This useEffect clean the meta data from post when block is deleted.
    useEffect(() => {
        return () => {
            updateTheMeta();
        }
    }, []);

    useEffect(() => {
        if (props.attributes.profId) {
            updateTheMeta();
            async function go() {
                const response = await apiFetch({
                    path: `/featuredProfessor/v1/getHTML?professorId=${props.attributes.profId}`,
                    method: "GET"
                });
                setThePreview(response);
            }
            go();
        }
    }, [props.attributes.profId]);

    function updateTheMeta() {
        const professorsForMeta = wp.data.select("core/block-editor")
            .getBlocks()
            .filter(block => block.name == "ourplugin/featured-professor")
            .map(block => block.attributes.profId)
            .filter((value, index, arr) => {
                return arr.indexOf(value) == index
            });

        wp.data.dispatch("core/editor").editPost({ meta: { featuredProfessor: professorsForMeta } })
    }

    const professors = useSelect(select => {
        return select("core").getEntityRecords("postType", "professor", { per_page: -1 });
    });

    if (professors == undefined) return <p>Loading...</p>

    return (
        <div className="featured-professor-wrapper">
            <div className="professor-select-container">
                <select onChange={(e) => props.setAttributes({
                    profId: e.target.value
                })} >
                    <option value="">{__("Select a professor", 'featured-professor')}</option>
                    {professors.map(professor => {
                        return (
                            <option value={professor.id} selected={props.attributes.profId == professor.id}>{professor.title.rendered}</option>
                        )
                    })}
                </select>
            </div>
            <div dangerouslySetInnerHTML={{ __html: thePreview }}></div>
        </div>
    )
}
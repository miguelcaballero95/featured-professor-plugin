import "./index.scss"
import { useSelect } from "@wordpress/data"
import { useState, useEffect } from "react";
import apiFetch from "@wordpress/api-fetch";

const __ = wp.i18n.__;

// This is the block registration
wp.blocks.registerBlockType("my-plugin/featured-professor", {
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

/**
 * This is the edit component of the block.
 * 
 * @param {*} props 
 * @returns 
 */
function EditComponent(props) {

    const [thePreview, setThePreview] = useState("");

    // This useEffect clean the meta data from post when block is deleted.
    useEffect(() => {
        return () => {
            updateTheMeta();
        }
    }, []);

    // This useEffect is responsible for fetching the HTML from the server and set it to the state.
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

    // This function is responsible for updating the meta data of the post.
    function updateTheMeta() {
        const professorsForMeta = wp.data.select("core/block-editor")
            .getBlocks()
            .filter(block => block.name == "my-plugin/featured-professor")
            .map(block => block.attributes.profId)
            .filter((value, index, arr) => {
                return arr.indexOf(value) == index
            });

        // This is the function to update the meta data of the post.
        wp.data.dispatch("core/editor").editPost({ meta: { featuredProfessor: professorsForMeta } })
    }

    // This is the function to get the professors from the server.
    const professors = useSelect(select => {
        return select("core").getEntityRecords("postType", "professor", { per_page: -1 });
    });

    // If professors are not loaded yet, show loading...
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
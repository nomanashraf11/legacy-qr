import { ErrorMessage, Field, FieldArray, Form, Formik } from "formik";
import * as Yup from "yup";
import { IoAdd, IoClose } from "react-icons/io5";
import { Spinner } from "./Spinner";
import { checkFileType, objectToFormData, regex } from "../utils";
import { API_BASE_URL } from "../config";
import { toast } from "react-toastify";
import { getAuthToken, useAppSelector } from "../redux";
import { useMutation } from "@tanstack/react-query";
import { useParams } from "react-router-dom";
import { FileItem } from "./FileItem";
import ReactPlayer from "react-player";
import { FileUploader } from "react-drag-drop-files";
const fileTypes = [
  // Image formats
  "JPG",
  "JPEG",
  "PNG",
  "GIF",
  "WEBP",
  "SVG",
  "BMP",
  "TIFF",
  "TIF",
  "ICO",
  "HEIC",
  "HEIF",
  "AVIF",

  // Video formats
  "MP4",
  "MOV",
  "AVI",
  "MKV",
  "WEBM",
  "FLV",
  "WMV",
  "MPEG",
  "MPG",
  "3GP",
];

const MAX_PHOTOS = 50;
const MAX_VIDEOS = 5;

export const UploadPhotosModal = ({ open, setOpen, data, refetch }) => {
  const { id } = useParams();
  const token = useAppSelector(getAuthToken);

  const {
    mutate,
    isPending: isLoading,
    mutateAsync: mutateFileUpload,
  } = useMutation({
    mutationFn: async (formData) => {
      const response = await fetch(`${API_BASE_URL}/${id}/add_photo`, {
        method: "POST",
        body: formData,
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${token}`,
          "X-Custom-Header": "header value",
        },
      }).then((res) => res.json());
      return response;
    },
  });

  const handleClose = () => {
    setOpen(false);
  };

  const initialValues = {
    image: [],
    youtube: "",
  };

  const imageValidationSchema = Yup.object().shape({
    image: Yup.array()
      .of(
        Yup.object().shape({
          file: Yup.mixed()
            .test(
              "fileType",
              "Invalid file type. Only images and videos are allowed.",
              (value) => {
                if (!value) return true;
                const allowedTypes = [...imageTypes, ...videoType];
                return allowedTypes.includes(value.type);
              }
            )
            .test(
              "fileSize",
              "File size should be less than or equal to 300MB",
              (value) => {
                if (!value) return true;
                return value.size <= 300 * 1024 * 1024;
              }
            ),
        })
      )
      .min(1, "At least one image or video is required")
      .max(5, "You can upload maximum 5 images or videos at a time")
      .required("Image or video is required"),
  });

  const youtubeValidationSchema = Yup.object().shape({
    youtube: Yup.string()
      .matches(regex.youtube, "Invalid YouTube URL")
      .required("YouTube link is required"),
  });

  let validationSchema = Yup.lazy((values) => {
    if (values.image.length > 0) {
      return imageValidationSchema;
    } else if (values.youtube) {
      return youtubeValidationSchema;
    } else {
      return imageValidationSchema.concat(youtubeValidationSchema);
    }
  });

  const existingPhotosCount =
    data?.Photos?.filter((item) => checkFileType(item.file) === "image")
      .length || 0;
  const existingVideosCount =
    data?.Photos?.filter((item) => checkFileType(item.file) === "video")
      .length || 0;

  const handleSubmit = async (values, { resetForm }) => {
    if (values.youtube) {
      const formData = objectToFormData({
        link: values.youtube,
      });
      mutate(formData, {
        onSuccess(response) {
          if (response.status === 201) {
            toast.success(response.message);
            refetch();
            resetForm();
          } else {
            toast.error("Link upload failed");
          }
        },
        onError: (err) => {
          console.log(err);
        },
      });
    } else {
      let success = [];
      for (let index = 0; index < values.image.length; index++) {
        const item = values.image[index];
        const formData = objectToFormData({
          image: item.file,
          caption: item.caption,
        });

        if (
          item.file.type.includes("image") &&
          existingPhotosCount + 1 <= MAX_PHOTOS
        ) {
          try {
            const response = await mutateFileUpload(formData);
            if (response.status === 201) {
              success.push(true);
            } else {
              toast.error("Image upload failed");
              success.push(false);
            }
          } catch (error) {
            console.log(error);
          }
        } else if (
          item.file.type.includes("video") &&
          existingVideosCount + 1 <= MAX_VIDEOS
        ) {
          try {
            const response = await mutateFileUpload(formData);
            if (response.status === 201) {
              success.push(true);
            } else {
              toast.error("Video upload failed");
              success.push(false);
            }
          } catch (error) {
            console.log(error);
          }
        }
      }

      let allTrue = success.every((value) => value === true);

      if (allTrue) {
        toast.success("Files uploaded successfully");
      }
      refetch();
      resetForm();
      setOpen(false);
    }
  };

  return (
    <Formik
      validateOnMount
      validateOnChange
      validateOnBlur
      enableReinitialize
      initialValues={initialValues}
      onSubmit={handleSubmit}
      validationSchema={validationSchema}
    >
      {({ values, resetForm, setFieldValue, isValid, handleBlur, errors }) => (
        <Form>
          <div
            className={
              `${
                data?.dark_theme ? "bg-black " : "bg-white"
              } backdrop-filter backdrop-blur-sm bg-opacity-60 overflow-x-hidden fixed top-0 right-0 left-0 bottom-0 max-h-full !z-50 flex justify-center items-center w-full md:inset-0 transition-all duration-200 ease-in-out ` +
              (open ? "block" : "hidden")
            }
          >
            <div className="relative p-4 w-full max-w-[500px] md:max-w-6xl max-h-full">
              <div
                className={`${
                  data?.dark_theme ? "bg-black " : "bg-white"
                }  relative  rounded-lg shadow`}
              >
                <div className="flex items-center justify-center p-4 border-b relative border-white/5 rounded-t">
                  <h3 className="sm:text-xl text-lg font-semibold text-center w-full">
                    Update Photos and Videos
                  </h3>
                  <div className="absolute top-0 right-0 left-0 bottom-0 flex items-center justify-end p-4">
                    <button
                      onClick={() => {
                        handleClose();
                        resetForm();
                      }}
                      type="button"
                      className={`${
                        data?.dark_theme ? "bg-black " : "bg-white"
                      }  text-gray-400 transition-all duration-200 ease-in-out  bg-opacity-20 hover:bg-opacity-25 active:bg-opacity-30 rounded-full text-sm w-8 h-8 ms-auto inline-flex justify-center items-center`}
                    >
                      <IoClose size={24} />
                    </button>
                  </div>
                </div>

                <div className="grid md:grid-cols-2 gap-4 p-4 md:p-6 max-h-[570px] overflow-auto hide-scrollbar">
                  <div className="md:pr-20">
                    <div className="space-y-4">
                      <p className="sm:text-base text-sm">
                        To add photos and videos click below. You can add up to
                        (5) photos at a time and (1) video at a time. For videos
                        over one minute or 300mb, please consider uploading via
                        YouTube.
                      </p>
                      <div>
                        <div className="relative h-24 sm:h-36 w-full">
                          <label
                            className={`${
                              data?.dark_theme
                                ? "active:bg-[#333333] hover:bg-[#222222] border-[#333333] text-gray-400 hover:text-white"
                                : "active:bg-[#f4f1f1] hover:bg-[#f4f1f1] border-[#f4f1f1] text-black hover:text-black"
                            }  select-none  h-full w-full flex items-center justify-center gap-2 rounded-md border cursor-pointer transition-all duration-300 ease-in-out `}
                          >
                            <div className="flex flex-col">
                              <div className="flex gap-6">
                                <IoAdd size={24} />
                                Add or Drop Photo / Video
                              </div>
                              <FileUploader
                                multiple={true}
                                classes="opacity-0"
                                onBlur={handleBlur}
                                handleChange={(files) => {
                                  const fileArray = Array.from(files);

                                  const imageObj = fileArray.map((file) => ({
                                    file,
                                    caption: "",
                                  }));

                                  setFieldValue("image", [
                                    ...values.image,
                                    ...imageObj,
                                  ]);
                                }}
                                name="profile_picture"
                                type="file"
                                hidden
                                types={fileTypes}
                              />
                            </div>
                          </label>
                          {!errors.youtube && values.youtube && (
                            <div className="absolute top-0 left-0 right-0 bottom-0" />
                          )}
                        </div>
                        {typeof errors.image === "string" && (
                          <div className="text-red-400 text-xs">
                            {errors.image}
                          </div>
                        )}
                      </div>
                      <div className="w-full flex items-center gap-4">
                        <span className="h-[0.5px] border-white/15 border-t w-full" />
                        <span className="text-sm text-white/50">or</span>
                        <span className="h-[0.5px] border-white/15 border-t w-full" />
                      </div>
                      <div>
                        <label
                          className={`${
                            data?.dark_theme ? "text-white" : "text-black"
                          } block mb-1 text-sm font-medium `}
                          htmlFor="youtube"
                        >
                          YouTube Link
                        </label>
                        <Field
                          onBlur={handleBlur}
                          disabled={values.image.length > 0}
                          name="youtube"
                          placeholder="YouTube link here"
                          className={
                            (errors.youtube
                              ? " border-red-600 "
                              : "border-white/20") +
                            `border outline-none text-sm disabled:opacity-50 rounded-lg block w-full p-2.5  ${
                              data?.dark_theme
                                ? "text-white bg-[#333333] border-white/20 placeholder-gray-400"
                                : " text-black bg-white"
                            }`
                          }
                        />
                        <ErrorMessage
                          className="text-red-400 text-xs"
                          name="youtube"
                          component="div"
                        />
                      </div>
                    </div>
                    <div className="w-full pt-5">
                      <button
                        disabled={!isValid || isLoading}
                        type="submit"
                        className="flex items-center justify-center gap-2 disabled:text-gray-400 disabled:bg-white/20 hover:bg-white/70 w-full text-[#333333] bg-white/90 active:bg-white/90 font-medium rounded-lg px-5 py-2 text-center transition-all duration-200 ease-in-out"
                      >
                        {isLoading && <Spinner />} Submit
                      </button>
                    </div>
                  </div>
                  <div className={"max-h-[400px] overflow-auto"}>
                    {values.image.length > 0 && (
                      <div className="space-y-6">
                        <p className="sm:text-base text-sm">
                          You selected photos/videos
                        </p>

                        <FieldArray name="image">
                          {({ remove }) => (
                            <div
                              className={
                                "grid grid-cols-2 sm:grid-cols-3 pr-4 gap-5"
                              }
                            >
                              {values.image.map((fileObj, index) => (
                                <div key={index}>
                                  <div
                                    key={index}
                                    className={
                                      "relative w-full rounded-lg [&_video]:object-cover overflow-hidden h-24 max-h-24 " +
                                      (errors.image?.[index]?.file
                                        ? "border border-red-400"
                                        : "")
                                    }
                                  >
                                    {fileObj.file?.type.includes("image") ? (
                                      <img
                                        className="w-full h-full object-cover"
                                        src={URL.createObjectURL(fileObj.file)}
                                      />
                                    ) : (
                                      <ReactPlayer
                                        width={"100%"}
                                        playsinline
                                        height={"100%"}
                                        muted
                                        url={URL.createObjectURL(fileObj.file)}
                                      />
                                    )}

                                    <button
                                      key={index}
                                      onClick={() => {
                                        remove(index);
                                      }}
                                      type="button"
                                      className="absolute top-0.5 right-0.5 text-gray-400 transition-all duration-200 ease-in-out bg-[#242526] hover:bg-opacity-90 active:bg-[#242526] rounded-full text-sm w-5 h-5 inline-flex justify-center items-center"
                                    >
                                      <IoClose />
                                    </button>
                                  </div>
                                  {errors.image?.[index]?.file && (
                                    <div className="text-red-400 text-[10px]">
                                      {errors.image?.[index]?.file}
                                    </div>
                                  )}
                                  <div className="mt-2">
                                    <Field
                                      name={`image[${index}].caption`}
                                      placeholder="Enter caption..."
                                      className={
                                        errors.image &&
                                        errors.image[index] &&
                                        errors.image[index].caption
                                          ? " border-red-600 "
                                          : "border-white/20" +
                                            " border outline-none text-sm disabled:opacity-50 rounded-lg block w-full py-1.5 px-2.5 bg-[#333333] border-white/20 placeholder-gray-400 text-white"
                                      }
                                    />
                                    <ErrorMessage
                                      className="text-red-400 text-xs"
                                      name={`image[${index}].caption`}
                                      component="div"
                                    />
                                  </div>
                                </div>
                              ))}
                            </div>
                          )}
                        </FieldArray>
                      </div>
                    )}
                    {data?.Photos?.length > 0 && (
                      <div
                        className={
                          "space-y-6 " + (values.image.length > 0 ? "mt-5" : "")
                        }
                      >
                        <p className="sm:text-base text-sm">
                          You uploaded photos/videos
                        </p>
                        <div className="grid grid-cols-3 gap-5 pr-4">
                          {data?.Photos?.map((item, index) => (
                            <FileItem
                              key={index}
                              item={item}
                              refetch={refetch}
                            />
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Form>
      )}
    </Formik>
  );
};

const imageTypes = [
  "image/jpeg",
  "image/jpg",
  "image/png",
  "image/gif",
  "image/heic",
];

const videoType = [
  "video/3g2",
  "video/hevc",
  "video/3gp",
  "video/asf",
  "video/avi",
  "video/flv",
  "video/m4v",
  "video/mov",
  "video/mp4",
  "video/mpg",
  "video/mpeg",
  "video/mkv",
  "video/rm",
  "video/swf",
  "video/vob",
  "video/wmv",
  "video/webm",
  "video/ogg",
  "video/ogv",
  "video/mts",
  "video/m2ts",
  "video/ts",
  "video/quicktime",
];
